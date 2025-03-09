<?php

namespace Hyvor\Internal\Auth;

use Faker\Factory;
use Hyvor\Internal\InternalApi\InternalApi;
use Illuminate\Support\Collection;
use Symfony\Component\DependencyInjection\Container;

/**
 * @phpstan-import-type AuthUserArrayPartial from AuthUser
 */
final class AuthFake implements AuthInterface
{

    /**
     * If $userDatabase is set, users will be searched (in fromX() methods) from this collection
     * Results will only be returned if the search is matched
     * If it is not set, all users will always be matched using fake data (for testing)
     * @var Collection<int, AuthUser>|null
     */
    private ?Collection $userDatabase = null;

    /**
     * Currently logged-in user
     */
    public ?AuthUser $user = null;

    /**
     * @param AuthUser|AuthUserArrayPartial|null $user
     */
    public function __construct(null|AuthUser|array $user = null)
    {
        if (is_array($user)) {
            $user = self::generateUser($user);
        }
        $this->user = $user;
    }

    /**
     * Laravel-only
     * @param AuthUser|AuthUserArrayPartial|null $user
     */
    public static function enable(null|AuthUser|array $user = null): void
    {
        $fake = new self();
        if (is_array($user)) {
            $user = self::generateUser($user);
        }
        $fake->user = $user;
        app()->singleton(
            Auth::class,
            fn() => $fake
        );
    }

    /**
     * Symfony-only
     * @param AuthUser|AuthUserArrayPartial|null $user
     */
    public static function enableForSymfony(Container $container, null|AuthUser|array $user = null): void
    {
        $fake = new self();
        if (is_array($user)) {
            $user = self::generateUser($user);
        }
        $fake->user = $user;
        $container->set(AuthInterface::class, $fake);
    }

    public function check(string $cookie): false|AuthUser
    {
        return $this->user ?: false;
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
        if ($this->userDatabase !== null) {
            return $this->userDatabase->firstWhere($key, $value);
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
        if ($this->userDatabase !== null) {
            return $this->userDatabase->whereIn($key, $values)
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

    /**
     * @param iterable<int, AuthUser|AuthUserArrayPartial> $users
     */
    public static function databaseSet(iterable $users = []): void
    {
        $fake = app(Auth::class);
        assert($fake instanceof self);

        $fake->userDatabase = collect($users)
            ->map(function ($user) {
                if ($user instanceof AuthUser) {
                    return $user;
                }
                return self::generateUser($user);
            });
    }

    /**
     * @return Collection<int, AuthUser>|null
     */
    public static function databaseGet(): ?Collection
    {
        $fake = app(Auth::class);
        assert($fake instanceof self);
        return $fake->userDatabase;
    }

    public static function databaseClear(): void
    {
        $fake = app(Auth::class);
        assert($fake instanceof self);
        $fake->userDatabase = null;
    }

    /**
     * @param AuthUser|AuthUserArrayPartial $user
     */
    public static function databaseAdd($user): void
    {
        $fake = app(Auth::class);
        assert($fake instanceof self);
        if ($fake->userDatabase === null) {
            $fake->userDatabase = collect([]);
        }
        $fake->userDatabase->push(
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
