<?php

namespace Hyvor\Internal\Tests\Bundle\Security;

use Hyvor\Internal\Auth\Auth;
use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Bundle\Security\HyvorAuthenticator;
use Hyvor\Internal\Tests\SymfonyTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

#[CoversClass(HyvorAuthenticator::class)]
class HyvorAuthenticatorTest extends SymfonyTestCase
{

    private function getAuthenticator(bool $user = false): HyvorAuthenticator
    {
        $authFake = new AuthFake($user ? ['id' => 1, 'username' => 'test_user'] : null);
        return new HyvorAuthenticator($authFake);
    }

    public function test_authentication_without_cookie(): void
    {
        $request = new Request();
        $authenticator = $this->getAuthenticator();

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('HYVOR session cookie not found');

        $authenticator->authenticate($request);
    }

    public function test_authentication_with_cookie_no_user(): void
    {
        $request = new Request();
        $request->cookies->set(Auth::HYVOR_SESSION_COOKIE_NAME, 'invalid_cookie');
        $authenticator = $this->getAuthenticator();

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('User not logged in');

        $authenticator->authenticate($request);
    }

    public function test_with_user(): void
    {
        $request = new Request();
        $request->cookies->set(Auth::HYVOR_SESSION_COOKIE_NAME, 'valid_cookie');
        $authenticator = $this->getAuthenticator(true);

        $passport = $authenticator->authenticate($request);
        $this->assertInstanceOf(SelfValidatingPassport::class, $passport);

        $badges = $passport->getBadges();
        $this->assertCount(1, $badges);

        $userBadge = $badges[UserBadge::class];
        $this->assertInstanceOf(UserBadge::class, $userBadge);
        $this->assertEquals('test_user', $userBadge->getUserIdentifier());

        $loader = $userBadge->getUserLoader();
        $this->assertNotNull($loader);
        $this->assertInstanceOf(AuthUser::class, $loader('test_user'));
    }

    public function test_cover_other_methods(): void
    {
        $authenticator = $this->getAuthenticator();
        $request = new Request();

        // supports should always return true
        $this->assertTrue($authenticator->supports($request));

        // onAuthenticationSuccess should return null
        $token = $this->createMock(TokenInterface::class);
        $this->assertNull($authenticator->onAuthenticationSuccess($request, $token, 'main'));

        // onAuthenticationFailure should return null
        $exception = $this->createMock(AuthenticationException::class);
        $this->assertNull($authenticator->onAuthenticationFailure($request, $exception));
    }

}