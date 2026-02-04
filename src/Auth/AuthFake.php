<?php

namespace Hyvor\Internal\Auth;

use Faker\Factory;
use Hyvor\Internal\Auth\Dto\Me;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;

/**
 * @phpstan-import-type AuthUserArrayPartial from AuthUser
 */
final class AuthFake implements AuthInterface
{

    private ?AuthUserOrganization $organization;

    /**
     * If $usersDatabase is set, users will be searched (in fromX() methods) from this collection
     * Results will only be returned if the search is matched
     * If it is not set, all users will always be matched using fake data (for testing)
     * @var AuthUser[]|null
     */
    private ?array $usersDatabase = null;

    /**
     * Currently logged-in user
     */
    public ?AuthUser $user = null;

    private static ?Container $symfonyContainer = null;

    /**
     * @param AuthUser|AuthUserArrayPartial|null $user
     * @param iterable<int, AuthUser|AuthUserArrayPartial>|null $usersDatabase
     */
    public function __construct(
        null|AuthUser|array $user = null,
        null|AuthUserOrganization $organization = null,
        ?iterable $usersDatabase = null
    ) {
        if (is_array($user)) {
            $user = self::generateUser($user);
        }
        $this->user = $user;
        $this->organization = $organization;
        $this->usersDatabase = $usersDatabase ? self::getAuthUsersFromPartial($usersDatabase) : null;
    }

    public function __destruct()
    {
        self::$symfonyContainer = null;
    }

    /**
     * Laravel-only
     * @param AuthUser|AuthUserArrayPartial|null $user
     * @param iterable<int, AuthUser|AuthUserArrayPartial>|null $usersDatabase
     */
    public static function enable(
        null|AuthUser|array $user = null,
        null|AuthUserOrganization $organization = null,
        ?iterable $usersDatabase = null
    ): void {
        $fake = new self($user, $organization, $usersDatabase);
        app()->singleton(
            AuthInterface::class,
            fn() => $fake
        );
    }

    /**
     * Symfony-only
     * @param AuthUser|AuthUserArrayPartial|null $user
     * @param iterable<int, AuthUser|AuthUserArrayPartial>|null $usersDatabase
     */
    public static function enableForSymfony(
        Container $container,
        null|AuthUser|array $user = null,
        null|AuthUserOrganization $organization = null,
        ?iterable $usersDatabase = null
    ): void {
        $fake = new self();
        if (is_array($user)) {
            $user = self::generateUser($user);
        }
        $fake->user = $user;
        $fake->organization = $organization;
        $fake->usersDatabase = $usersDatabase !== null ? self::getAuthUsersFromPartial($usersDatabase) : null;
        self::$symfonyContainer = $container;
        $container->set(AuthInterface::class, $fake);
    }

    public function me(string|Request $request): ?Me
    {
        return $this->user ? new Me($this->user, $this->organization) : null;
    }

    public function authUrl(string $page, null|string|Request $redirect = null): string
    {
        $redirect = Auth::resolveRedirect($redirect);
        $redirectQuery = $redirect ? '?redirect=' . urlencode($redirect) : '';
        return 'https://hyvor.com/' . $page . $redirectQuery;
    }

    /**
     * @param iterable<int> $ids
     * @return AuthUser[]
     */
    public function fromIds(iterable $ids): array
    {
        return $this->multiSearch('id', $ids);
    }

    public function fromId(int $id): ?AuthUser
    {
        return $this->singleSearch('id', $id);
    }

    /**
     * @param iterable<string> $emails
     */
    public function fromEmails(iterable $emails)
    {
        // this is email => AuthUser
        $response = $this->multiSearch('email', $emails);

        // convert it to email => AuthUser[]
        return array_map(fn($user) => [$user], $response);
    }

    public function fromEmail(string $email): array
    {
        return $this->singleSearchMultipleHit('email', $email);
    }

    /**
     * @param iterable<string> $usernames
     */
    public function fromUsernames(iterable $usernames)
    {
        return $this->multiSearch('username', $usernames);
    }

    public function fromUsername(string $username): ?AuthUser
    {
        return $this->singleSearch('username', $username);
    }

    /**
     * @param 'id' | 'username' | 'email' $key
     */
    private function singleSearch(string $key, string|int $value): ?AuthUser
    {
        if ($this->usersDatabase !== null) {
            return array_find(
                $this->usersDatabase,
                fn(AuthUser $user) => $user->{$key} === $value
            );
        }

        // @phpstan-ignore-next-line
        return $this->generateUser([$key => $value]);
    }

    /**
     * @return AuthUser[]
     */
    private function singleSearchMultipleHit(string $key, string|int $value): array
    {
        if ($this->usersDatabase !== null) {
            $matches = [];
            foreach ($this->usersDatabase as $user) {
                if ($user->{$key} === $value) {
                    $matches[] = $user;
                }
            }

            return $matches;
        }

        // @phpstan-ignore-next-line
        return [ $this->generateUser([$key => $value]) ];
    }

    /**
     * @template T of int|string
     * @param iterable<T> $values
     * @return array<T, AuthUser>
     */
    private function multiSearch(string $key, iterable $values): array
    {
        if ($this->usersDatabase !== null) {
            $result = [];
            foreach ($this->usersDatabase as $item) {
                if (in_array($item->$key, (array)$values)) {
                    $result[$item->$key] = $item;
                }
            }
            return $result;
        }

        $result = [];
        foreach ($values as $value) {
            // @phpstan-ignore-next-line
            $user = self::generateUser([$key => $value]);
            $result[$user->$key] = $user;
        }
        return $result;
    }

    private static function getFakeFromContainer(): self
    {
        if (self::$symfonyContainer) {
            // symfony
            $fake = self::$symfonyContainer->get(AuthInterface::class);
        } else {
            // laravel
            $fake = app(AuthInterface::class);
        }

        assert($fake instanceof self);
        return $fake;
    }

    /**
     * @param iterable<int, AuthUser|AuthUserArrayPartial> $users
     * @return AuthUser[]
     */
    private static function getAuthUsersFromPartial($users): array
    {
        return array_map(function ($user) {
            if ($user instanceof AuthUser) {
                return $user;
            }
            return self::generateUser($user);
        }, (array)$users);
    }

    /**
     * @param iterable<int, AuthUser|AuthUserArrayPartial> $users
     */
    public static function databaseSet(iterable $users = []): void
    {
        $fake = self::getFakeFromContainer();
        $fake->usersDatabase = self::getAuthUsersFromPartial($users);
    }

    /**
     * @return AuthUser[]|null
     */
    public static function databaseGet(): ?array
    {
        $fake = self::getFakeFromContainer();
        return $fake->usersDatabase;
    }

    public static function databaseClear(): void
    {
        $fake = self::getFakeFromContainer();
        $fake->usersDatabase = null;
    }

    /**
     * @param AuthUser|AuthUserArrayPartial $user
     */
    public static function databaseAdd($user): void
    {
        $fake = self::getFakeFromContainer();

        if ($fake->usersDatabase === null) {
            $fake->usersDatabase = [];
        }
        $fake->usersDatabase[] = $user instanceof AuthUser ? $user : self::generateUser($user);
    }

    /**
     * @param AuthUserArrayPartial $fill
     */
    public static function generateUser(array $fill = []): AuthUser
    {
        $faker = Factory::create();

        return AuthUser::fromArray(array_merge([
            'id' => $faker->randomNumber(),
            'username' => $faker->name(),
            'name' => $faker->name(),
            'email' => $faker->email(),
            'email_relay' => $faker->userName() . '@relay.hyvor.com',
            'picture_url' => 'https://picsum.photos/100/100',
        ], $fill));
    }

}
