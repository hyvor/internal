<?php

namespace Hyvor\Internal\Auth;

use Faker\Factory;
use Illuminate\Support\Collection;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;

/**
 * @phpstan-import-type AuthUserArrayPartial from AuthUser
 */
final class AuthFake implements AuthInterface
{

    /**
     * If $usersDatabase is set, users will be searched (in fromX() methods) from this collection
     * Results will only be returned if the search is matched
     * If it is not set, all users will always be matched using fake data (for testing)
     * @var Collection<int, AuthUser>|null
     */
    private ?Collection $usersDatabase = null;

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
        ?iterable $usersDatabase = null
    ) {
        if (is_array($user)) {
            $user = self::generateUser($user);
        }
        $this->user = $user;
        $this->usersDatabase = $usersDatabase ? self::getAuthUsersFromPartial($usersDatabase) : null;
    }

    /**
     * Laravel-only
     * @param AuthUser|AuthUserArrayPartial|null $user
     * @param iterable<int, AuthUser|AuthUserArrayPartial>|null $usersDatabase
     */
    public static function enable(
        null|AuthUser|array $user = null,
        ?iterable $usersDatabase = null
    ): void {
        $fake = new self($user, $usersDatabase);
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
        ?iterable $usersDatabase = null
    ): void {
        $fake = new self();
        if (is_array($user)) {
            $user = self::generateUser($user);
        }
        $fake->user = $user;
        $fake->usersDatabase = $usersDatabase ? self::getAuthUsersFromPartial($usersDatabase) : null;
        self::$symfonyContainer = $container;
        $container->set(AuthInterface::class, $fake);
    }

    public function check(string $cookie): false|AuthUser
    {
        return $this->user ?: false;
    }

    public function authUrl(string $page, null|string|Request $redirect = null): string
    {
        $redirect = Auth::resolveRedirect($redirect);
        $redirectQuery = $redirect ? '?redirect=' . urlencode($redirect) : '';
        return 'https://hyvor.com/' . $page . $redirectQuery;
    }

    /**
     * @param iterable<int> $ids
     * @return Collection<int, AuthUser>
     */
    public function fromIds(iterable $ids)
    {
        return $this->multiSearch('id', $ids);
    }

    public function fromId(int $id): ?AuthUser
    {
        return $this->singleSearch('id', $id);
    }

    /**
     * @param iterable<string> $emails
     * @return Collection<string, AuthUser>
     */
    public function fromEmails(iterable $emails)
    {
        return $this->multiSearch('email', $emails);
    }

    public function fromEmail(string $email): ?AuthUser
    {
        return $this->singleSearch('email', $email);
    }

    /**
     * @param iterable<string> $usernames
     * @return Collection<string, AuthUser>
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
            return $this->usersDatabase->firstWhere($key, $value);
        }

        // @phpstan-ignore-next-line
        return $this->generateUser([$key => $value]);
    }

    /**
     * @template T of int|string
     * @param iterable<T> $values
     * @return Collection<T, AuthUser>
     */
    private function multiSearch(string $key, iterable $values): Collection
    {
        if ($this->usersDatabase !== null) {
            return $this->usersDatabase->whereIn($key, $values)
                ->keyBy($key);
        }

        // @phpstan-ignore-next-line
        return collect($values)
            ->map(function ($value) use ($key) {
                // @phpstan-ignore-next-line
                return self::generateUser([$key => $value]);
            })
            ->keyBy($key);
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
     * @return Collection<int, AuthUser>
     */
    private static function getAuthUsersFromPartial($users): Collection
    {
        return collect($users)
            ->map(function ($user) {
                if ($user instanceof AuthUser) {
                    return $user;
                }
                return self::generateUser($user);
            });
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
     * @return Collection<int, AuthUser>|null
     */
    public static function databaseGet(): ?Collection
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
            $fake->usersDatabase = collect([]);
        }
        $fake->usersDatabase->push(
            $user instanceof AuthUser ? $user : self::generateUser($user)
        );
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
