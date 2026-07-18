<?php

namespace Unit\CloudApi;

use Hyvor\Internal\Auth\Oidc\Testing\OidcTestingUtils;
use Hyvor\Internal\CloudApi\CloudApiService;
use Hyvor\Internal\CloudApi\CloudJwt;
use Hyvor\Internal\CloudApi\Scope\PostScope;
use Hyvor\Internal\CloudApi\Scope\ScopeBuilder;
use Hyvor\Internal\CloudApi\Scope\TalkScope;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\Tests\SymfonyTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(CloudApiService::class)]
#[CoversClass(CloudJwt::class)]
class CloudApiServiceTest extends SymfonyTestCase
{

    use ClockSensitiveTrait;

    public function test_create_jwt_token(): void
    {

        $this->mockTime('2024-06-01 12:00:00');

        /** @var CloudApiService $cloudApiService */
        $cloudApiService = $this->getContainer()->get(CloudApiService::class);

        $scopeBuilder = new ScopeBuilder();
        $scopeBuilder->addScopes(Component::TALK, [TalkScope::WEBSITE_READ]);

        $jwtToken = $cloudApiService->createJwtToken(10, $scopeBuilder);

        $this->assertSame(10, $jwtToken->getOrganizationId());
        $this->assertSame('2024-06-01 13:00:00', new \DateTimeImmutable('@' . $jwtToken->getExpiresAt())->format('Y-m-d H:i:s'));

        $this->assertSame(
            [
                'iss' => 'https://hyvor.com',
                'sub' => '10',
                'iat' => 1717243200,
                'nbf' => 1717243200,
                'exp' => 1717246800,
                'scope' => 'talk:website.read'
            ],
            $jwtToken->toArray()
        );

    }


    public function test_decode_jwt_token_from_http_jwks(): void
    {
        $keys = OidcTestingUtils::generateKey('cloud-api-key');

        $httpResponse = new JsonMockResponse($keys['jwks']);
        $this->getContainer()
            ->set(HttpClientInterface::class, new MockHttpClient($httpResponse));

        /** @var CloudApiService $cloudApiService */
        $cloudApiService = $this->getContainer()->get(CloudApiService::class);

        $scopeBuilder = new ScopeBuilder();
        $scopeBuilder->addScopes(Component::POST, [PostScope::ORG_NEWSLETTERS_CREATE, PostScope::ORG_NEWSLETTERS_READ]);
        $jwtToken = $cloudApiService->createJwtToken(10, $scopeBuilder)->encode($keys['privateKeyPem'], 'cloud-api-key');

        $decodedJwt = $cloudApiService->decodeJwtToken($jwtToken);
        $this->assertSame(10, $decodedJwt->getOrganizationId());
        $this->assertSame(['org.newsletters.create', 'org.newsletters.read'], $decodedJwt->getScopesFor(Component::POST));
        $this->assertSame([], $decodedJwt->getScopesFor(Component::TALK));
    }

}
