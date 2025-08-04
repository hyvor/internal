<?php

namespace Hyvor\Internal\Tests\Unit\Auth\Oidc;

use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Auth\Oidc\OidcAuth;
use Hyvor\Internal\Auth\Oidc\OidcUserService;
use Hyvor\Internal\Bundle\Entity\OidcUser;
use Hyvor\Internal\Tests\SymfonyTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

#[CoversClass(OidcAuth::class)]
#[CoversClass(OidcUserService::class)]
class OidcAuthTest extends SymfonyTestCase
{

    use OidcUserFactory;

    private function getOidcAuth(): OidcAuth
    {
        $oidcAuth = $this->container->get(OidcAuth::class);
        assert($oidcAuth instanceof OidcAuth);
        return $oidcAuth;
    }

    public function test_check_not_logged_in(): void
    {
        $oidc = $this->getOidcAuth();
        $requestNotLoggedIn = Request::create('/');
        $requestNotLoggedIn->setSession($this->createMock(SessionInterface::class));
        $this->assertFalse($oidc->check($requestNotLoggedIn));
    }

    public function test_check_has_session_but_wrong_user_id(): void
    {
        $oidc = $this->getOidcAuth();
        $session = $this->createMock(SessionInterface::class);
        $session
            ->method('get')
            ->with('oidc_user_id')
            ->willReturn(1);

        $requestNotLoggedIn = Request::create('/');
        $requestNotLoggedIn->setSession($session);

        $this->assertFalse($oidc->check($requestNotLoggedIn));
    }

    public function test_check_logged_in(): void
    {
        $oidcUser = $this->createOidcUser();
        $this->em->persist($oidcUser);
        $this->em->flush();

        $oidc = $this->getOidcAuth();
        $session = $this->createMock(SessionInterface::class);
        $session
            ->method('get')
            ->with('oidc_user_id')
            ->willReturn($oidcUser->getId());

        $requestNotLoggedIn = Request::create('/');
        $requestNotLoggedIn->setSession($session);

        $user = $oidc->check($requestNotLoggedIn);
        $this->assertInstanceOf(AuthUser::class, $user);

        $this->assertSame($oidcUser->getId(), $user->id);
        $this->assertSame($oidcUser->getEmail(), $user->email);
        $this->assertSame($oidcUser->getName(), $user->name);
        $this->assertSame($oidcUser->getPictureUrl(), $user->picture_url);
        $this->assertSame($oidcUser->getWebsiteUrl(), $user->website_url);
    }

    public function test_auth_url(): void
    {
        $oidcAuth = $this->getOidcAuth();

        // Test login URL
        $loginUrl = $oidcAuth->authUrl('login');
        $this->assertSame('/api/oidc/login', $loginUrl);

        // Test logout URL
        $logoutUrl = $oidcAuth->authUrl('logout');
        $this->assertEquals('/api/oidc/logout', $logoutUrl);

        // Test with custom redirect
        $customRedirect = '/custom-page';
        $loginUrlWithRedirect = $oidcAuth->authUrl('login', $customRedirect);
        $this->assertStringContainsString('redirect=%2Fcustom-page', $loginUrlWithRedirect);
    }

}