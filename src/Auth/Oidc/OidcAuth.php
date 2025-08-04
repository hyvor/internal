<?php

namespace Hyvor\Internal\Auth\Oidc;

use Hyvor\Internal\Auth\Auth;
use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Auth\AuthUser;
use Symfony\Component\HttpFoundation\Request;

/**
 * Only Symfony!
 */
class OidcAuth implements AuthInterface
{

    public function __construct(
        private OidcUserService $oidcUserService,
    ) {
    }

    public function check(Request $request): false|AuthUser
    {
        $session = $request->getSession();
        $oidcUser = $this->oidcUserService->getCurrentUser($session);

        if ($oidcUser === null) {
            return false;
        }

        return AuthUser::fromOidcUser($oidcUser);
    }

    public function authUrl(string $page, string|Request|null $redirect = null): string
    {
        $redirect = Auth::resolveRedirect($redirect);
        $redirect = $redirect ? '?redirect=' . urlencode($redirect) : '';

        if ($page === 'logout') {
            // logout always redirects to the homepage
            return '/api/oidc/logout';
        } else {
            return '/api/oidc/login' . $redirect;
        }
    }

    public function fromIds(iterable $ids): array
    {
        $oidcUsers = $this->oidcUserService->findByIds($ids);
        $indexedUsers = [];
        foreach ($oidcUsers as $oidcUser) {
            $indexedUsers[$oidcUser->getId()] = AuthUser::fromOidcUser($oidcUser);
        }
        return $indexedUsers;
    }

    public function fromId(int $id): ?AuthUser
    {
        $oidcUser = $this->oidcUserService->findById($id);
        return $oidcUser ? AuthUser::fromOidcUser($oidcUser) : null;
    }

    public function fromEmails(iterable $emails)
    {
        // TODO
    }

    public function fromEmail(string $email): array
    {
        // TODO
        return [];
    }

    public function fromUsernames(iterable $usernames)
    {
        throw new \LogicException('OIDC does not implement fromUsernames().');
    }

    public function fromUsername(string $username): ?AuthUser
    {
        throw new \LogicException('OIDC does not implement fromUsername().');
    }
}