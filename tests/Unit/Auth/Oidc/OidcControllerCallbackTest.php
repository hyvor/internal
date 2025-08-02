<?php

namespace Hyvor\Internal\Tests\Unit\Auth\Oidc;

use Hyvor\Internal\Auth\Oidc\OidcConfig;
use Hyvor\Internal\Auth\Oidc\OidcApiService;
use Hyvor\Internal\Bundle\Controller\OidcController;
use Hyvor\Internal\Tests\SymfonyTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[CoversClass(OidcController::class)]
#[CoversClass(OidcConfig::class)]
#[CoversClass(OidcApiService::class)]
class OidcControllerCallbackTest extends SymfonyTestCase
{

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

    public function gets_id_token_and_signs_up(): void
    {
        //
    }

}