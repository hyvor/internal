<?php

namespace Hyvor\Internal\Billing\Dto;

use Hyvor\Internal\Billing\Exception\LicenseOfCombinationNotFoundException;
use Hyvor\Internal\Billing\License\License;
use Hyvor\Internal\Component\Component;

/**
 * @phpstan-type LicenseArray array{user_id: int, resource_id: ?int, license: ?string}
 */
readonly class LicensesCollection
{

    public function __construct(
        /**
         * @var LicenseArray[]
         */
        private array $licenses,

        private Component $component,
    ) {
    }


    /**
     * @throws LicenseOfCombinationNotFoundException when the userid resourceid pair is not found
     */
    public function of(int $userId, ?int $resourceId): ?License
    {
        foreach ($this->licenses as $license) {
            if ($license['user_id'] === $userId && $license['resource_id'] === $resourceId) {
                $licenseClass = $this->component->license();

                $license = $license['license'];
                return $license ? $licenseClass::unserialize($license) : null;
            }
        }

        // @codeCoverageIgnoreStart
        throw new LicenseOfCombinationNotFoundException(
            "License of userId: $userId and resourceId: $resourceId not found"
        );
        // @codeCoverageIgnoreEnd
    }


    /**
     * @return LicenseArray[]
     */
    public function all(): array
    {
        return $this->licenses;
    }

}