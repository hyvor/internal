<?php

namespace Hyvor\Internal\Auth;

use Faker\Factory;
use Hyvor\Internal\Auth\Dto\Me;
use Hyvor\Internal\Auth\Dto\Organization;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;

/**
 * @phpstan-import-type AuthUserArrayPartial from AuthUser
 * @phpstan-import-type OrganizationArrayPartial from Organization
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
     * If $organizationsDatabase is set, organizations will be searched from this array
     * Otherwise, fake data will always be generated
     * @var Organization[]|null
     */
    private ?array $organizationsDatabase = null;

    /**
     * Currently logged-in user
     */
    public ?AuthUser $user = null;

    private static ?Container $symfonyContainer = null;

    /**
     * @param AuthUser|AuthUserArrayPartial|null $user
     * @param iterable<int, AuthUser|AuthUserArrayPartial>|null $usersDatabase
     * @param array<OrganizationArrayPartial|Organization> $organizationsDatabase
     */
    public function __construct(
        null|AuthUser|array $user = null,
        null|AuthUserOrganization $organization = null,
        ?iterable $usersDatabase = null,
        ?array $organizationsDatabase = null,
    ) {
        if (is_array($user)) {
            $user = self::generateUser($user);
        }
        $this->user = $user;
        $this->organization = $organization;
        $this->usersDatabase = $usersDatabase ? self::getAuthUsersFromPartial($usersDatabase) : null;
        $this->organizationsDatabase = $organizationsDatabase ? self::getOrganizationsFromPartial($organizationsDatabase) : null;
    }

    public function __destruct()
    {
        self::$symfonyContainer = null;
    }

    /**
     * Laravel-only
     * @param AuthUser|AuthUserArrayPartial|null $user
     * @param iterable<int, AuthUser|AuthUserArrayPartial>|null $usersDatabase
     * @param ?array<Organization|OrganizationArrayPartial> $organizationsDatabase
     */
    public static function enable(
        null|AuthUser|array $user = null,
        null|AuthUserOrganization $organization = null,
        ?iterable $usersDatabase = null,
        ?array $organizationsDatabase = null,
    ): void {
        $fake = new self($user, $organization, $usersDatabase, $organizationsDatabase);
        app()->singleton(
            AuthInterface::class,
            fn() => $fake
        );
    }

    /**
     * Symfony-only
     * @param AuthUser|AuthUserArrayPartial|null $user
     * @param iterable<int, AuthUser|AuthUserArrayPartial>|null $usersDatabase
     * @param ?array<Organization|OrganizationArrayPartial> $organizationsDatabase
     */
    public static function enableForSymfony(
        Container $container,
        null|AuthUser|array $user = null,
        null|AuthUserOrganization $organization = null,
        ?iterable $usersDatabase = null,
        ?array $organizationsDatabase = null,
    ): void {
        $fake = new self();
        if (is_array($user)) {
            $user = self::generateUser($user);
        }
        $fake->user = $user;
        $fake->organization = $organization;
        $fake->usersDatabase = $usersDatabase !== null ? self::getAuthUsersFromPartial($usersDatabase) : null;
        $fake->organizationsDatabase = $organizationsDatabase !== null ? self::getOrganizationsFromPartial($organizationsDatabase) : null;
        self::$symfonyContainer = $container;
        $container->set(AuthInterface::class, $fake);
    }

    public function me(string|Request $request): ?Me
    {
        return $this->user ? new Me($this->user, $this->organization) : null;
    }

    /**
     * @param int[] $organizationIds
     * @return array<int, Organization> Indexed by organization ID.
     */
    public function organizations(
        array $organizationIds,
        bool $includeBillingInfo = false,
        bool $includeCreatedUser = false,
    ): array
    {
        $organizations = $this->doGetOrganizations($organizationIds);

        // recreate objects based on include settings
        // so that we can mimic the exact behaviour of missing properties
        $return = [];

        foreach ($organizations as $id => $organization) {
            $org = new Organization(
                $organization->getId(),
                $organization->getName(),
                $organization->getMembersCount()
            );

            if ($includeBillingInfo) {
                $org->setBillingEmail($organization->getBillingEmail());
                $org->setBillingAddress($organization->getBillingAddress());
            }

            if ($includeCreatedUser) {
                $org->setCreatedUser($organization->getCreatedUser());
            }

            $return[$id] = $org;
        }

        return $return;
    }

    /**
     * @return array<int, Organization>
     */
    private function doGetOrganizations(array $organizationIds): array
    {
        if ($this->organizationsDatabase !== null) {
            $result = [];
            foreach ($this->organizationsDatabase as $org) {
                if (in_array($org->getId(), $organizationIds)) {
                    $result[$org->getId()] = $org;
                }
            }
            return $result;
        }

        $result = [];
        foreach ($organizationIds as $organizationId) {
            $organization = self::generateOrganization([
                'id' => $organizationId
            ]);
            $result[$organizationId] = $organization;
        }
        return $result;
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
     * @param array<int, Organization|OrganizationArrayPartial> $orgs
     * @return array
     */
    private static function getOrganizationsFromPartial(array $orgs): array
    {
        return array_map(function ($org) {
            if ($org instanceof Organization) {
                return $org;
            }
            return self::generateOrganization($org);
        }, $orgs);
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

    public static function generateOrganization(array $fill = []): Organization
    {
        $faker = Factory::create();

        return Organization::fromArray(array_merge([
            'id' => $faker->randomNumber(),
            'name' => $faker->name(),
            'member_count' => $faker->randomNumber(),
            'created_user' => self::generateUser(),
            'billing_email' => $faker->email(),
            'billing_address' => [
                'line1' => $faker->streetAddress(),
                'city' => $faker->city(),
                'state' => $faker->city(),
                'postal_code' => $faker->postcode(),
                'country' => $faker->countryCode(),
            ]
        ], $fill));
    }

}
