<?php

namespace Hyvor\Internal;

use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Auth\AuthUserOrganization;
use Hyvor\Internal\Billing\License\License;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicenseType;
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

    public function organization(): ?AuthUserOrganization
    {
        return new AuthUserOrganization(
            id: 1,
            name: 'HYVOR',
            role: 'admin'
        );
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
    public function license(int $organizationId, Component $component): ?License
    {
        $licenseClass = $component->license();
        return $licenseClass::trial(); // trial defaults
    }

    /**
     * sets a trial license for all organizations
     * @param int[] $organizationIds
     * @return array<int, ResolvedLicense>
     */
    public function licenses(array $organizationIds, Component $component): array
    {
        $licenses = [];
        $licenseClass = $component->license();
        foreach ($organizationIds as $organizationId) {
            $licenses[$organizationId] = new ResolvedLicense(
                ResolvedLicenseType::TRIAL,
                $licenseClass::trial()
            );
        }
        return $licenses;
    }

    /**
     * If App\InternalFake exists, it returns its instance. Otherwise, it returns an instance of this class.
     */
    public static function getInstance(): InternalFake
    {
        /** @var class-string<InternalFake> $class */
        $class = class_exists('App\InternalFake') ? 'App\InternalFake' : InternalFake::class;
        return new $class;
    }

}
