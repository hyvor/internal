<?php

namespace Hyvor\Internal\Auth;

use Hyvor\Internal\Component\Component;
use Hyvor\Internal\Component\InstanceUrlResolver;
use Hyvor\Internal\InternalApi\Exceptions\InternalApiCallFailedException;
use Hyvor\Internal\InternalApi\InternalApi;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

    /**
     * @param string $page page in core to redirect to
     * @param string|null $redirect absolute URL to redirect back to
     */
    private function redirectTo(
        string $page,
        null|string $redirect
    ): RedirectResponse {
        $pos = strpos($page, '?');
        $placeholder = $pos === false ? '?' : '&';

        $redirectQuery = '';
        if (is_string($redirect)) {
            $redirectQuery = $placeholder . 'redirect=' . urlencode($redirect);
        }

        $fullUrl = $this->instanceUrlResolver->publicUrlOfCore() . '/' . $page . $redirectQuery;

        return new RedirectResponse($fullUrl);
    }

    public function login(null|string|Request $redirect = null): RedirectResponse
    {
        return $this->redirectTo('login', $redirect);
    }

    public function signup(null|string|Request $redirect = null): RedirectResponse
    {
        return $this->redirectTo('signup', $redirect);
    }

    public function logout(null|string|Request $redirect = null): RedirectResponse
    {
        return $this->redirectTo('logout', $redirect);
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
