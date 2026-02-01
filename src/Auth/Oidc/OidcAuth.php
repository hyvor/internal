<?php

namespace Hyvor\Internal\Auth\Oidc;

use Hyvor\Internal\Auth\Auth;
use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Auth\AuthUserOrganization;
use Hyvor\Internal\Auth\Dto\Me;
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

    public function me(Request $request): ?Me
    {
        $session = $request->getSession();
        $oidcUser = $this->oidcUserService->getCurrentUser($session);

        if ($oidcUser === null) {
            return null;
        }

        return new Me(
            AuthUser::fromOidcUser($oidcUser),
            new AuthUserOrganization(
                0,
                'Default',
                'admin'
            ),
        );
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

    public function fromEmails(iterable $emails): array
    {
        $oidcUsers = $this->oidcUserService->findByEmails((array)$emails);

        $indexedUsers = [];

        foreach ($oidcUsers as $oidcUser) {
            $indexedUsers[$oidcUser->getEmail()][] = AuthUser::fromOidcUser($oidcUser);
        }

        return $indexedUsers;
    }

    public function fromEmail(string $email): array
    {
        $oidcUsers = $this->oidcUserService->findByEmail($email);
        return array_map(
            fn($oidcUser) => AuthUser::fromOidcUser($oidcUser),
            $oidcUsers
        );
    }

    /**
     * @codeCoverageIgnore
     */
    public function fromUsernames(iterable $usernames)
    {
        throw new \LogicException('OIDC does not implement fromUsernames().');
    }

    /**
     * @codeCoverageIgnore
     */
    public function fromUsername(string $username): ?AuthUser
    {
        throw new \LogicException('OIDC does not implement fromUsername().');
    }
}
