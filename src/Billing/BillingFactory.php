<?php

namespace Hyvor\Internal\Billing;

use Hyvor\Internal\Bundle\BillingFakeLicenseProvider;
use Hyvor\Internal\InternalConfig;
use Hyvor\Internal\InternalFake;

class BillingFactory
{

    public function __construct(
        private InternalConfig $internalConfig,
        private Billing $billing,
    ) {
    }

    public function create(): BillingInterface
    {
        if ($this->internalConfig->isFake()) {
            $fake = InternalFake::getInstance();
            $fakeLicenseProvider = new BillingFakeLicenseProvider(get_class($fake));
            return new BillingFake(
                $this->internalConfig,
                [$fakeLicenseProvider, 'license'],
                [$fakeLicenseProvider, 'licenses']
            );
        }
        return $this->billing;
    }

}