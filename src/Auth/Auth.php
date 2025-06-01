<?php

namespace Hyvor\Internal\Auth;

use Hyvor\Internal\Component\Component;
use Hyvor\Internal\Component\InstanceUrlResolver;
use Hyvor\Internal\InternalApi\Exceptions\InternalApiCallFailedException;
use Hyvor\Internal\InternalApi\InternalApi;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Request;

/**
 * @phpstan-import-type AuthUserArray from AuthUser
 */
class Auth implements AuthInterface
{

    public const string HYVOR_SESSION_COOKIE_NAME = 'authsess';

    public function __construct(
        private InternalApi $internalApi,
        private InstanceUrlResolver $instanceUrlResolver,
    ) {
    }

    /**
     * @throws InternalApiCallFailedException
     */
    public function check(string $cookie): false|AuthUser
    {
        if (empty($cookie)) {
            return false;
        }

        $response = $this->internalApi->call(
            Component::CORE,
            '/auth/check',
            [
                'cookie' => $cookie
            ]
        );

        /** @var null|AuthUserArray $user */
        $user = $response['user'];

        return is_array($user) ? AuthUser::fromArray($user) : false;
    }

    public function authUrl(string $page, null|string|Request $redirect = null): string
    {
        $redirect = self::resolveRedirect($redirect);
        $redirectQuery = $redirect ? '?redirect=' . urlencode($redirect) : '';
        return $this->instanceUrlResolver->publicUrlOfCore() . '/' . $page . $redirectQuery;
    }

    public static function resolveRedirect(null|string|Request $redirect): ?string
    {
        if (is_string($redirect)) {
            return $redirect;
        } elseif ($redirect instanceof Request) {
            return $redirect->getUri();
        }
        return null;
    }

    /**
     * @template T of int|string
     * @param 'ids'|'emails'|'usernames' $field
     * @param iterable<T> $values
     * @return Collection<T, AuthUser> keyed by the field
     */
    protected function getUsersByField(string $field, iterable $values): Collection
    {
        $response = $this->internalApi->call(
            Component::CORE,
            '/auth/users/from/' . $field,
            [
                $field => (array)$values
            ]
        );

        $users = collect($response);
        return $users->map(fn($user) => AuthUser::fromArray($user));
    }

    /**
     * @param iterable<int> $ids
     * @return Collection<int, AuthUser>
     */
    public function fromIds(iterable $ids)
    {
        return $this->getUsersByField('ids', $ids);
    }

    public function fromId(int $id): ?AuthUser
    {
        return $this->fromIds([$id])->get($id);
    }

    /**
     * @param iterable<string> $emails
     * @return Collection<string, AuthUser>
     */
    public function fromEmails(iterable $emails)
    {
        return $this->getUsersByField('emails', $emails);
    }

    public function fromEmail(string $email): ?AuthUser
    {
        return $this->fromEmails([$email])->get($email);
    }

    /**
     * @param iterable<string> $usernames
     * @return Collection<string, AuthUser>
     */
    public function fromUsernames(iterable $usernames)
    {
        return $this->getUsersByField('usernames', $usernames);
    }

    public function fromUsername(string $username): ?AuthUser
    {
        return $this->fromUsernames([$username])->get($username);
    }

}
