<?php

namespace Hyvor\Internal\Tests\Bundle\Controller;

use Firebase\JWT\JWT;
use Hyvor\Internal\Auth\Oidc\OidcApiService;
use Hyvor\Internal\Auth\Oidc\OidcConfig;
use Hyvor\Internal\Auth\Oidc\OidcUserService;
use Hyvor\Internal\Bundle\Controller\OidcController;
use Hyvor\Internal\Bundle\Entity\OidcUser;
use Hyvor\Internal\Tests\SymfonyTestCase;
use Hyvor\Internal\Tests\Unit\Auth\Oidc\OidcUserFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[CoversClass(OidcController::class)]
#[CoversClass(OidcConfig::class)]
#[CoversClass(OidcApiService::class)]
#[CoversClass(OidcUserService::class)]
class OidcCallbackTest extends SymfonyTestCase
{

    use OidcUserFactory;

    private function setTestOidcEnv(): void
    {
        $_ENV['OIDC_ISSUER_URL'] = 'https://example.com';
        $_ENV['OIDC_CLIENT_ID'] = 'test_client_id';
        $_ENV['OIDC_CLIENT_SECRET'] = 'test_client_secret';
    }

    public function test_fails_on_invalid_state(): void
    {
        $this->setTestOidcEnv();

        $request = Request::create('/api/oidc/callback', 'GET');
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);
        $request->query->set('state', 'invalid_state');
        $request->getSession()->set('oidc_state', 'valid_state');

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Invalid state parameter.');
        $this->kernel->handle($request, catch: false);
    }

    public function test_fails_when_code_empty(): void
    {
        $this->setTestOidcEnv();

        $request = Request::create('/api/oidc/callback', 'GET');
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);
        $request->query->set('state', 'valid_state');
        $request->getSession()->set('oidc_state', 'valid_state');

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Authorization code not provided.');
        $this->kernel->handle($request, catch: false);
    }

    /**
     * @return array{privateKeyPem: string, publicKeyPem: string, rsa: array<string, mixed>, jwks: array<string, mixed>}
     */
    private function generateKey(): array
    {
        $config = [
            "private_key_bits" => 2048,
            "private_key_type" => \OPENSSL_KEYTYPE_RSA,
        ];
        $res = openssl_pkey_new($config);
        assert($res !== false);
        openssl_pkey_export($res, $privateKeyPem);
        $keyDetails = openssl_pkey_get_details($res);
        assert($keyDetails !== false);
        $publicKeyPem = $keyDetails['key'];
        $rsa = $keyDetails['rsa'];

        $jwks = [
            "keys" => [
                [
                    "kty" => "RSA",
                    "use" => "sig",
                    "kid" => "example-key-id-1", // match with the JWT header
                    "alg" => "RS256",
                    "n" => $this->base64urlEncode($rsa['n']),
                    "e" => $this->base64urlEncode($rsa['e']),
                ]
            ]
        ];

        return [
            'privateKeyPem' => $privateKeyPem,
            'publicKeyPem' => $publicKeyPem,
            'rsa' => $rsa,
            'jwks' => $jwks,
        ];
    }

    private function base64urlEncode(mixed $data): string
    {
        assert(is_string($data));
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function wellKnownResponse(): JsonMockResponse
    {
        return new JsonMockResponse([
            'issuer' => 'https://example.com',
            'authorization_endpoint' => 'https://example.com/authorize',
            'token_endpoint' => 'https://example.com/token',
            'userinfo_endpoint' => 'https://example.com/userinfo',
            'jwks_uri' => 'https://example.com/jwks',
        ]);
    }

    private function createRequest(
        string $sessionState = 'valid_state',
        string $queryState = 'valid_state',
        string $code = 'valid_code',
        string $nonce = 'my-nonce',
        string $redirectUrl = '/back'
    ): Request {
        $request = Request::create('/api/oidc/callback');
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);
        $request->getSession()->set('oidc_state', $sessionState);
        $request->getSession()->set('oidc_nonce', $nonce);
        $request->getSession()->set('oidc_redirect', $redirectUrl);

        $request->query->set('state', $queryState);
        $request->query->set('code', $code);

        return $request;
    }

    /**
     * @param array<mixed>|null $payload
     */
    private function createIdToken(
        string $privateKeyPem,
        ?array $payload = null,
        bool $extendPayload = false,
    ): string {
        $now = time();

        $defaultPayload = [
            "iss" => "https://issuer.com",
            "sub" => "user123",
            "exp" => $now + 3600,
            "iat" => $now,
            "auth_time" => $now,
            "nonce" => "my-nonce",
            "name" => "Jane",
            "email" => "jane@example.com",
            "email_verified" => true,
        ];
        if ($payload === null) {
            $payload = $defaultPayload;
        } else {
            $payload = $extendPayload ? array_merge($defaultPayload, $payload) : $payload;
        }

        $headers = [
            'kid' => 'example-key-id-1',
            'alg' => 'RS256',
            'typ' => 'JWT',
        ];

        return JWT::encode($payload, $privateKeyPem, 'RS256', null, $headers);
    }

    public function test_gets_id_token_and_signs_up(): void
    {
        [
            'privateKeyPem' => $privateKeyPem,
            'jwks' => $jwks
        ] = $this->generateKey();

        $jwt = $this->createIdToken($privateKeyPem);

        $wellKnownResponse = $this->wellKnownResponse();
        $idTokenResponse = new JsonMockResponse(['id_token' => $jwt]);
        $jwksResponse = new JsonMockResponse($jwks);

        $this->setHttpClientResponse([$wellKnownResponse, $idTokenResponse, $jwksResponse]);
        $this->setTestOidcEnv();

        $request = $this->createRequest();
        $response = $this->kernel->handle($request, catch: false);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/back', $response->getTargetUrl());

        $users = $this->em->getRepository(OidcUser::class)->findAll();
        $this->assertCount(1, $users);

        $user = $users[0];
        $this->assertSame('user123', $user->getSub());
        $this->assertSame('Jane', $user->getName());
        $this->assertSame('jane@example.com', $user->getEmail());
        $this->assertSame('https://issuer.com', $user->getIss());

        $this->assertSame('https://example.com/.well-known/openid-configuration', $wellKnownResponse->getRequestUrl());
        $this->assertSame('https://example.com/token', $idTokenResponse->getRequestUrl());
        $this->assertSame('https://example.com/jwks', $jwksResponse->getRequestUrl());

        $tokenRequestData = $idTokenResponse->getRequestOptions();
        $body = $tokenRequestData['body'];
        parse_str($body, $parsedBody);

        $this->assertSame('authorization_code', $parsedBody['grant_type']);
        $this->assertSame('valid_code', $parsedBody['code']);
        $this->assertSame('http://localhost/api/oidc/callback', $parsedBody['redirect_uri']);
        $this->assertSame('test_client_id', $parsedBody['client_id']);
        $this->assertSame('test_client_secret', $parsedBody['client_secret']);
    }

    public function test_when_id_token_api_call_fails(): void
    {
        $wellKnownResponse = $this->wellKnownResponse();
        $idTokenResponse = new JsonMockResponse(info: ['status' => 500, 'error' => 'Internal Server Error']);

        $this->setHttpClientResponse([$wellKnownResponse, $idTokenResponse]);
        $request = $this->createRequest();

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Unable to authenticate Internal Server Error');
        $this->kernel->handle($request, catch: false);
    }

    public function test_jwks_parsing(): void
    {
        $wellKnownResponse = $this->wellKnownResponse();
        $idTokenResponse = new JsonMockResponse(['id_token' => 'dummy.jwt.token']);
        $jwksResponse = new JsonMockResponse(['keys' => []]);

        $this->setHttpClientResponse([$wellKnownResponse, $idTokenResponse, $jwksResponse]);
        $request = $this->createRequest();

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Invalid JWKS: JWK Set did not contain any keys');

        $this->kernel->handle($request, catch: false);
    }

    public function test_id_token_decoding_fail(): void
    {
        [
            'privateKeyPem' => $privateKeyPem,
            'jwks' => $jwks
        ] = $this->generateKey();

        $idToken = $this->createIdToken($privateKeyPem, payload: ['iss' => 1]);

        $wellKnownResponse = $this->wellKnownResponse();
        $idTokenResponse = new JsonMockResponse(['id_token' => $idToken]);
        $jwksResponse = new JsonMockResponse($jwks);

        $this->setHttpClientResponse([$wellKnownResponse, $idTokenResponse, $jwksResponse]);
        $request = $this->createRequest();

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Invalid ID Token: The type of the "iss" attribute for class');

        $this->kernel->handle($request, catch: false);
    }

    public function test_error_on_validation_failure(): void
    {
        [
            'privateKeyPem' => $privateKeyPem,
            'jwks' => $jwks
        ] = $this->generateKey();

        $idToken = $this->createIdToken($privateKeyPem, payload: ['email' => 'wrong'], extendPayload: true);

        $wellKnownResponse = $this->wellKnownResponse();
        $idTokenResponse = new JsonMockResponse(['id_token' => $idToken]);
        $jwksResponse = new JsonMockResponse($jwks);

        $this->setHttpClientResponse([$wellKnownResponse, $idTokenResponse, $jwksResponse]);
        $request = $this->createRequest();

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('ID token validation failed: [email] This value is not a valid email address.');

        $this->kernel->handle($request, catch: false);
    }

    public function test_fails_when_nonce_does_not_match(): void
    {
        [
            'privateKeyPem' => $privateKeyPem,
            'jwks' => $jwks
        ] = $this->generateKey();

        $idToken = $this->createIdToken($privateKeyPem, payload: ['nonce' => 'wrong'], extendPayload: true);

        $wellKnownResponse = $this->wellKnownResponse();
        $idTokenResponse = new JsonMockResponse(['id_token' => $idToken]);
        $jwksResponse = new JsonMockResponse($jwks);

        $this->setHttpClientResponse([$wellKnownResponse, $idTokenResponse, $jwksResponse]);
        $request = $this->createRequest();

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Invalid nonce in ID Token.');

        $this->kernel->handle($request, catch: false);
    }

    public function test_fails_when_email_not_verified(): void
    {
        [
            'privateKeyPem' => $privateKeyPem,
            'jwks' => $jwks
        ] = $this->generateKey();

        $idToken = $this->createIdToken($privateKeyPem, payload: ['email_verified' => false], extendPayload: true);

        $wellKnownResponse = $this->wellKnownResponse();
        $idTokenResponse = new JsonMockResponse(['id_token' => $idToken]);
        $jwksResponse = new JsonMockResponse($jwks);

        $this->setHttpClientResponse([$wellKnownResponse, $idTokenResponse, $jwksResponse]);
        $request = $this->createRequest();

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Email not verified. Only verified emails are allowed.');

        $this->kernel->handle($request, catch: false);
    }

    public function test_logs_in_and_updates_data(): void
    {
        [
            'privateKeyPem' => $privateKeyPem,
            'jwks' => $jwks
        ] = $this->generateKey();

        $idToken = $this->createIdToken(
            $privateKeyPem,
            payload: [
                'email' => 'new@test.com',
                'name' => 'New User',
                'picture' => 'https://example.com/picture.jpg',
                'website' => 'https://example.com/website',
            ],
            extendPayload: true
        );
        $oidcUser = $this->createOidcUser(
            email: 'old@test.com',
            name: 'Old User',
            iss: 'https://issuer.com',
            sub: 'user123',
        );
        $this->em->persist($oidcUser);
        $this->em->flush();

        $wellKnownResponse = $this->wellKnownResponse();
        $idTokenResponse = new JsonMockResponse(['id_token' => $idToken]);
        $jwksResponse = new JsonMockResponse($jwks);

        $this->setHttpClientResponse([$wellKnownResponse, $idTokenResponse, $jwksResponse]);
        $request = $this->createRequest();
        $response = $this->kernel->handle($request, catch: false);

        $this->assertInstanceOf(RedirectResponse::class, $response);

        $user = $this->em->getRepository(OidcUser::class)->find($oidcUser->getId());
        $this->assertNotNull($user);

        $this->assertSame('new@test.com', $user->getEmail());
        $this->assertSame('New User', $user->getName());
        $this->assertSame('https://example.com/picture.jpg', $user->getPictureUrl());
        $this->assertSame('https://example.com/website', $user->getWebsiteUrl());
    }

}