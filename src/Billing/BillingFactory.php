<?php

namespace Hyvor\Internal\Billing;

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
            return new BillingFake(
                $this->internalConfig,
                fn ($organizationIds, $component) => $fake->licenses($organizationIds, $component),
            );
        }
        return $this->billing;
    }

}
