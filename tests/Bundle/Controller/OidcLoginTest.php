<?php

namespace Hyvor\Internal\Tests\Bundle\Controller;

use Hyvor\Internal\Tests\SymfonyTestCase;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OidcLoginTest extends SymfonyTestCase
{

    public function test_handles_oidc_api_error(): void
    {
        $this->setHttpClientResponse(new MockResponse('{}', info: ['status' => 500]));

        $request = Request::create('/api/oidc/login');

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage("Missing key 'issuer' in discovery document.");
        $this->kernel->handle($request, catch: false);
    }

    public function test_handles_oidc_api_error_string_test(): void
    {
        $this->setHttpClientResponse(new JsonMockResponse([
            'issuer' => false
        ]));

        $request = Request::create('/api/oidc/login');

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage("Key 'issuer' in discovery document must be a string.");
        $this->kernel->handle($request, catch: false);
    }

    public function test_sets_session_and_redirects(): void
    {
        $_ENV['OIDC_CLIENT_ID'] = 'my-client-id';

        $this->setHttpClientResponse(new JsonMockResponse([
            'issuer' => 'https://example.com',
            'authorization_endpoint' => 'https://example.com/authorize',
            'token_endpoint' => 'https://example.com/token',
            'userinfo_endpoint' => 'https://example.com/userinfo',
            'jwks_uri' => 'https://example.com/jwks',
            'end_session_endpoint' => 'https://example.com/logout',
        ]));

        $sessionData = [];

        $session = $this->createMock(SessionInterface::class);
        $session->method('set')->willReturnCallback(fn($key, $value) => $sessionData[$key] = $value);

        $request = Request::create('/api/oidc/login');
        $request->setSession($session);

        $response = $this->kernel->handle($request, catch: false);
        $this->assertInstanceOf(RedirectResponse::class, $response);

        $this->assertStringStartsWith(
            'https://example.com/authorize?response_type=code&client_id=my-client-id&redirect_uri=http%3A%2F%2Flocalhost%2Fapi%2Foidc%2Fcallback&scope=openid+profile+email',
            $response->getTargetUrl()
        );
    }

}