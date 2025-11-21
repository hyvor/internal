<?php

namespace Hyvor\Internal\Billing;

use Hyvor\Internal\Billing\Dto\LicenseOf;
use Hyvor\Internal\Billing\Dto\LicensesCollection;
use Hyvor\Internal\Billing\License\License;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\InternalApi\Exceptions\InternalApiCallFailedException;

interface BillingInterface
{
    /**
     * @throws InternalApiCallFailedException
     */
    public function license(int $organizationId, ?int $resourceId, ?Component $component = null): ?License;

    /**
     * @param array<LicenseOf> $of
     * @throws InternalApiCallFailedException
     */
    public function licenses(array $of, ?Component $component = null): LicensesCollection;
}