<?php

namespace Hyvor\Internal\Auth;

use Illuminate\Support\Collection;

interface AuthInterface
{

    public function check(string $cookie): false|AuthUser;

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