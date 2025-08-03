<?php

namespace Hyvor\Internal\Auth;

use Illuminate\Support\Collection;
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
     * @return Collection<int, AuthUser>
     */
    public function fromIds(iterable $ids);

    public function fromId(int $id): ?AuthUser;

    /**
     * @param iterable<string> $emails
     * @return Collection<string, AuthUser>
     */
    public function fromEmails(iterable $emails);

    public function fromEmail(string $email): ?AuthUser;

    /**
     * @param iterable<string> $usernames
     * @return Collection<string, AuthUser>
     */
    public function fromUsernames(iterable $usernames);

    public function fromUsername(string $username): ?AuthUser;

}