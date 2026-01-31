<?php

namespace Hyvor\Internal\Billing\Usage;

use Hyvor\Internal\Billing\License\License;

/**
 * @template T of License = License
 * Create a class extending this class to abstract usage
 * Ex: storage in BLOGS
 */
abstract class UsageAbstract
{

    public function __construct()
    {
        $licenseType = $this->getLicenseType();
        $key = $this->getKey();
        assert(property_exists($licenseType, $key));
    }


    /**
     * @return class-string<T>
     */
    abstract public function getLicenseType(): string;

    abstract public function getKey(): string;

    abstract public function usageOfOrganization(int $organizationId): int;

    /**
     * Gets the currently allowed limit
     */
    public function getLimit(License $license): int
    {
        $key = $this->getKey();
        return $license->{$key};
    }

    /**
     * @param T $license
     * Checks if the usage limit has been reached by the organization
     * Use this to check on an action that could exceed the usage limit
     */
    public function hasReached(
        License $license,
        int $organizationId,
        bool $checkForExceed = false,
    ): bool {
        $usage = $this->usageOfOrganization($organizationId);
        $allowed = $this->getLimit($license);

        return $checkForExceed ?
            $usage > $allowed :
            $usage >= $allowed;
    }

    /**
     * @param T $license
     */
    public function hasExceeded(
        License $license,
        int $organizationId,
    ): bool {
        return $this->hasReached($license, $organizationId, true);
    }

}
