<?php

namespace Hyvor\Internal\Auth;

use Symfony\Component\HttpFoundation\Request;

interface AuthInterface
{

    public function check(Request $request): false|AuthUser;

    /**
     * Redirect to a login, signup, or logout page of the core
     *
     * @param 'login'|'signup'|'logout' $page The page to redirect to after authentication.
     * @param string|null|Request $redirect The URL to redirect to after authentication.
     *                                       If null, no redirection will be performed.
     *                                       If a string is provided, it should be an absolute URL.
     *                                       If a Request object is provided, the redirect URL will be created from it.
     */
    public function authUrl(string $page, null|string|Request $redirect = null): string;

    /**
     * @param iterable<int> $ids
     * @return array<int, AuthUser> Indexed by user ID.
     */
    public function fromIds(iterable $ids);

    public function fromId(int $id): ?AuthUser;

    /**
     * @param iterable<string> $emails
     * @return array<string, AuthUser[]> Indexed by email.
     */
    public function fromEmails(iterable $emails);

    /**
     * @return AuthUser[]
     */
    public function fromEmail(string $email): array;

    /**
     * OIDC does not support this method.
     * @param iterable<string> $usernames
     * @return array<string, AuthUser> Indexed by username.
     */
    public function fromUsernames(iterable $usernames);

    // OIDC does not support this method.
    public function fromUsername(string $username): ?AuthUser;

}