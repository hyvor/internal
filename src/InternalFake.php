<?php

namespace Hyvor\Internal;

use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Billing\Dto\LicenseOf;
use Hyvor\Internal\Billing\Dto\LicensesCollection;
use Hyvor\Internal\Billing\License\License;
use Hyvor\Internal\Component\Component;

/**
 * @phpstan-import-type AuthUserArrayPartial from AuthUser
 */
class InternalFake
{

    /**
     * It returns a default user with id 1.
     */
    public function user(): ?AuthUser
    {
        return AuthUser::fromArray([
            'id' => 1,
            'username' => 'alex',
            'name' => 'Alex Dornan',
            'email' => 'alex@hyvor.com',
            'picture_url' => 'https://picsum.photos/100/100',
        ]);
    }

    /**
     * @return array<int, AuthUser|AuthUserArrayPartial>|null
     */
    public function usersDatabase(): ?array
    {
        return null;
    }

    /**
     * Returns a default (trial) license of the component
     */
    public function license(int $userId, ?int $resourceId, Component $component): ?License
    {
        $licenseClass = $component->license();
        return new $licenseClass; // trial defaults
    }

    /**
     * Returns a collection of licenses for the given LicenseOf objects.
     * @@codeCoverageIgnore TODO: add coverage later
     * @param LicenseOf[] $of
     */
    public function licenses(array $of, Component $component): LicensesCollection
    {
        $licenses = [];
        $licenseClass = $component->license();
        foreach ($of as $licenseOf) {
            $licenses[] = [
                'user_id' => $licenseOf->userId,
                'resource_id' => $licenseOf->resourceId,
                'license' => (new $licenseClass)->serialize(),
            ];
        }
        return new LicensesCollection($licenses, $component);
    }

}
