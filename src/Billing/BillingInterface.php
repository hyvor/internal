<?php

namespace Hyvor\Internal\Billing;

use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\InternalApi\Exceptions\InternalApiCallFailedException;

interface BillingInterface
{
    /**
     * @throws InternalApiCallFailedException
     */
    public function license(int $organizationId, ?Component $component = null): ResolvedLicense;

    /**
     * @param int[] $organizationIds
     * @return array<int, ResolvedLicense> orgId keyed licenses
     * @throws InternalApiCallFailedException
     */
    public function licenses(array $organizationIds, ?Component $component = null): array;
}