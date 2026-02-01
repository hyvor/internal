<?php

namespace Hyvor\Internal\Bundle;

use Hyvor\Internal\Billing\License\License;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\InternalFake;

class BillingFakeLicenseProvider
{

    private InternalFake $internalFake;

    /**
     * @param class-string<InternalFake> $internalFakeClass
     */
    public function __construct(string $internalFakeClass)
    {
        $this->internalFake = new $internalFakeClass();
    }

    public function license(int $userId, Component $component): ?License
    {
        return $this->internalFake->license($userId, $component);
    }

    /**
     * @param int[] $organizationIds
     * @return array<int, ResolvedLicense>
     */
    public function licenses(array $organizationIds, Component $component): array
    {
        return $this->internalFake->licenses($organizationIds, $component);
    }

}
