<?php

namespace Hyvor\Internal\Tests\Bundle\Controller;

use Hyvor\Internal\Tests\SymfonyTestCase;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class OidcLogoutTest extends SymfonyTestCase
{

    public function test_logout_redirects_to_oidc_provider(): void
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

        $invalidated = false;

        $request = Request::create('/api/oidc/logout', server: ['HTTP_HOST' => 'relay.hyvor.com']);
        $session = $this->createMock(SessionInterface::class);
        $session->method('invalidate')->willReturnCallback(function () use (&$invalidated) {
            $invalidated = true;
            return true;
        });
        $request->setSession($session);
        $response = $this->kernel->handle($request, catch: false);

        $this->assertTrue($invalidated);
        $this->assertInstanceOf(RedirectResponse::class, $response);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertSame(
            'https://example.com/logout?client_id=my-client-id&post_logout_redirect_uri=http%3A%2F%2Frelay.hyvor.com',
            $response->getTargetUrl()
        );
    }

    public function test_redirects_to_homepage_if_api_fails(): void
    {
        $this->setHttpClientResponse(new JsonMockResponse('{}', info: ['status' => 500]));

        $request = Request::create('/api/oidc/logout', server: ['HTTP_HOST' => 'relay.hyvor.com']);
        $session = $this->createMock(SessionInterface::class);
        $request->setSession($session);

        $response = $this->kernel->handle($request, catch: false);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function test_redirects_to_homepage_if_no_logout_url_is_present_in_wellknown(): void
    {
        $this->setHttpClientResponse(new JsonMockResponse([
            'issuer' => 'https://example.com',
            'authorization_endpoint' => 'https://example.com/authorize',
            'token_endpoint' => 'https://example.com/token',
            'userinfo_endpoint' => 'https://example.com/userinfo',
            'jwks_uri' => 'https://example.com/jwks',
        ]));

        $request = Request::create('/api/oidc/logout', server: ['HTTP_HOST' => 'relay.hyvor.com']);
        $session = $this->createMock(SessionInterface::class);
        $request->setSession($session);

        $response = $this->kernel->handle($request, catch: false);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertSame('/', $response->getTargetUrl());
    }

}