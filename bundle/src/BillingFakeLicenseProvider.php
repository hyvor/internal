<?php

namespace Hyvor\Internal\Bundle;

use Hyvor\Internal\Billing\Dto\LicenseOf;
use Hyvor\Internal\Billing\Dto\LicensesCollection;
use Hyvor\Internal\Billing\License\License;
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

    public function license(int $userId, ?int $blogId, Component $component): ?License
    {
        return $this->internalFake->license($userId, $blogId, $component);
    }

    /**
     * @param LicenseOf[] $of
     */
    public function licenses(array $of, Component $component): LicensesCollection
    {
        return $this->internalFake->licenses($of, $component);
    }

}
