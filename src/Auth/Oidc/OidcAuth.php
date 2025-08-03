<?php

namespace Hyvor\Internal\Auth\Oidc;

use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Bundle\Entity\OidcUser;
use Symfony\Component\HttpFoundation\Request;

/**
 * Only Symfony!
 */
class OidcAuth implements AuthInterface
{

    public function __construct(
        private EntityManagerInterface $em,
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
        return '/api/oidc/login';
    }

    public function fromIds(iterable $ids)
    {
        // TODO: Implement fromIds() method.
    }

    public function fromId(int $id): ?AuthUser
    {
        return $this->em
            ->getRepository(OidcUser::class)
            ->find($id);
    }

    public function fromEmails(iterable $emails)
    {
        throw new \LogicException('OIDC email is not unique. Do not use fromEmails() method.');
    }

    public function fromEmail(string $email): ?AuthUser
    {
        throw new \LogicException('OIDC email is not unique. Do not use fromEmail() method.');
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