<?php

namespace Hyvor\Internal\Auth;

use Hyvor\Internal\Component\Component;
use Hyvor\Internal\Component\ComponentUrlResolver;
use Hyvor\Internal\InternalApi\Exceptions\InternalApiCallFailedException;
use Hyvor\Internal\InternalApi\InternalApi;
use Hyvor\Internal\InternalApi\InternalApiMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Collection;

/**
 * @phpstan-import-type AuthUserArray from AuthUser
 */
class Auth implements AuthInterface
{

    public function __construct(
        private InternalApi $internalApi
    ) {
    }

    public const HYVOR_SESSION_COOKIE_NAME = 'authsess';

    /**
     * @throws InternalApiCallFailedException
     */
    public function check(string $cookie): false|AuthUser
    {
        $response = $this->internalApi->call(
            Component::CORE,
            InternalApiMethod::POST,
            '/auth/check',
            [
                'cookie' => $cookie
            ]
        );

        /** @var null|AuthUserArray $user */
        $user = $response['user'];

        return is_array($user) ? AuthUser::fromArray($user) : false;
    }

    private function redirectTo(
        string $page,
        ?string $redirectPage = null
    ): RedirectResponse {
        $pos = strpos($page, '?');
        $placeholder = $pos === false ? '?' : '&';

        /** @var Request $request */
        $request = request();

        if ($redirectPage === null) {
            $redirectPage = $request->getPathInfo();
        }

        $redirectUrl = $redirectPage && str_starts_with($redirectPage, 'https://')
            ? $redirectPage
            : $request->getSchemeAndHttpHost() . $redirectPage;

        $redirect = $placeholder . 'redirect=' .
            urlencode($redirectUrl);

        return redirect(
            ComponentUrlResolver::getInstanceUrl() .
            '/' .
            $page .
            $redirect
        );
    }

    public function login(?string $redirect = null): RedirectResponse|Redirector
    {
        return $this->redirectTo('login', $redirect);
    }

    public function signup(?string $redirect = null): RedirectResponse|Redirector
    {
        return $this->redirectTo('signup', $redirect);
    }

    public function logout(?string $redirect = null): RedirectResponse|Redirector
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
        $response = InternalApi::call(
            Component::CORE,
            InternalApiMethod::POST,
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
