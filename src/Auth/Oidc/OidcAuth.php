<?php

namespace Hyvor\Internal\Auth\Oidc;

use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Auth\AuthUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Only Symfony!
 */
class OidcAuth implements AuthInterface
{

    public function __construct(
        private OidcWellKnownService $discoveryService,
        private OidcConfig $config,
        private RequestStack $requestStack,
    ) {
    }

    public function check(string|Request $request): false|AuthUser
    {
        assert($request instanceof Request, 'OpenIdAuth::check() expects a Request object.');

        // check JWT

        return false;
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
        // TODO: Implement fromId() method.
    }

    public function fromEmails(iterable $emails)
    {
        // TODO: Implement fromEmails() method.
    }

    public function fromEmail(string $email): ?AuthUser
    {
        // TODO: Implement fromEmail() method.
    }

    public function fromUsernames(iterable $usernames)
    {
        // TODO: Implement fromUsernames() method.
    }

    public function fromUsername(string $username): ?AuthUser
    {
        // TODO: Implement fromUsername() method.
    }
}