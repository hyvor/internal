<?php

namespace Hyvor\Internal\Billing;

use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Bundle\Comms\Exception\CommsApiFailedException;
use Hyvor\Internal\Component\Component;

interface BillingInterface
{
    /**
     * @throws CommsApiFailedException
     */
    public function license(int $organizationId, ?Component $component = null): ResolvedLicense;

    /**
     * @param int[] $organizationIds
     * @return array<int, ResolvedLicense> orgId keyed licenses
     * @throws CommsApiFailedException
     */
    public function licenses(array $organizationIds, ?Component $component = null): array;

    /**
     * @param string $idempotencyKey A unique key within the component to ensure the same usage isn't recorded twice.
     *  ex: orgId + date
     * @throws CommsApiFailedException
     */
    public function recordMeteredUsage(
        int $organizationId,
        int $amount,
        string $idempotencyKey,
        ?Component $component = null
    ): void;
}
