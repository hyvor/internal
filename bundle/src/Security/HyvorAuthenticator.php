<?php

namespace Hyvor\Internal\Bundle\Security;

use Hyvor\Internal\Auth\Auth;
use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Auth\AuthUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class HyvorAuthenticator extends AbstractAuthenticator
{

    public function __construct(
        private AuthInterface $auth
    ) {
    }


    public function supports(Request $request): ?bool
    {
        return true;
    }

    public function authenticate(Request $request): Passport
    {
        $cookie = $request->cookies->get(Auth::HYVOR_SESSION_COOKIE_NAME);

        if (!is_string($cookie)) {
            throw new AuthenticationException('Hyvor session cookie not found');
        }

        $user = $this->auth->check($cookie);

        if ($user === false) {
            throw new AuthenticationException('User not logged in');
        }

        return new SelfValidatingPassport(
            new UserBadge(
                $user->username,
                function (string $username) use ($user): AuthUser {
                    return $user;
                }
            )
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return null;
    }
}