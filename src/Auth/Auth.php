<?php

namespace Hyvor\Internal\Auth;

use Hyvor\Internal\Auth\Dto\Me;
use Hyvor\Internal\Bundle\Comms\CommsInterface;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\User\GetMe;
use Hyvor\Internal\Bundle\Comms\Exception\CommsApiFailedException;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\Component\InstanceUrlResolver;
use Hyvor\Internal\InternalApi\InternalApi;
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
        private CommsInterface $comms,
    ) {
    }

    /**
     * @throws CommsApiFailedException
     */
    public function me(Request $request): ?Me
    {
        $cookie = $request->cookies->get(self::HYVOR_SESSION_COOKIE_NAME);

        if (!$cookie) {
            return null;
        }

        $response = $this->comms->send(new GetMe($cookie));

        $user = $response->getUser();

        if (!$user) {
            return null;
        }

        return new Me($user, $response->getOrganization());
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
     * @return array<T, AuthUser> keyed by the field
     */
    protected function getUsersByField(string $field, iterable $values): array
    {
        $values = (array) $values;

        if (count($values) === 0) {
            return [];
        }

        $response = $this->internalApi->call(
            Component::CORE,
            '/auth/users/from/' . $field,
            [
                $field => $values
            ]
        );

        $return = [];

        foreach ($response as $index => $user) {
            $return[$index] = AuthUser::fromArray($user);
        }

        return $return;
    }

    /**
     * @param iterable<int> $ids
     * @return array<int, AuthUser>
     */
    public function fromIds(iterable $ids)
    {
        return $this->getUsersByField('ids', $ids);
    }

    public function fromId(int $id): ?AuthUser
    {
        return $this->fromIds([$id])[$id] ?? null;
    }

    /**
     * @param iterable<string> $emails
     * @return array<string, AuthUser[]>
     */
    public function fromEmails(iterable $emails): array
    {
        // this is email => AuthUser
        $response = $this->getUsersByField('emails', $emails);

        // convert it to email => AuthUser[]
        return array_map(fn($user) => [$user], $response);
    }

    public function fromEmail(string $email): array
    {
        return $this->fromEmails([$email])[$email] ?? [];
    }

    /**
     * @param iterable<string> $usernames
     * @return array<string, AuthUser>
     */
    public function fromUsernames(iterable $usernames): array
    {
        return $this->getUsersByField('usernames', $usernames);
    }

    public function fromUsername(string $username): ?AuthUser
    {
        return $this->fromUsernames([$username])[$username] ?? null;
    }

}
