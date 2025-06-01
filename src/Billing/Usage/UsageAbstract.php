<?php

namespace Hyvor\Internal\Billing\Usage;

use Hyvor\Internal\Billing\License\DerivedFrom;
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

    abstract public function usageOfUser(int $userId): int;

    abstract public function usageOfResource(int $resourceId): int;

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
     * Checks if the usage limit has been reached by the resource or the user
     * depending on the license derivedFrom
     * Use this to check on an action that could exceed the usage limit
     */
    public function hasReached(
        License $license,
        int $userId,
        ?int $resourceId = null,
        bool $checkForExceed = false,
    ): bool {
        $isResource = isset($license->derivedFrom) &&
            $license->derivedFrom === DerivedFrom::CUSTOM_RESOURCE &&
            $resourceId !== null;

        $usage = $isResource ?
            $this->usageOfResource($resourceId) :
            $this->usageOfUser($userId);

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
        int $userId,
        ?int $resourceId = null,
    ): bool {
        return $this->hasReached($license, $userId, $resourceId, true);
    }

}
