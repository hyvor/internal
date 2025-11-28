<?php

namespace Hyvor\Internal\Tests\Unit\Billing;

use Hyvor\Internal\Billing\Billing;
use Hyvor\Internal\Billing\BillingFactory;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Tests\SymfonyTestCase;

class BillingFactoryTest extends SymfonyTestCase
{

    public function test_fake(): void
    {
        $_ENV['HYVOR_FAKE'] = '1';

        $billingFactory = $this->container->get(BillingFactory::class);
        $this->assertInstanceOf(BillingFactory::class, $billingFactory);

        $billing = $billingFactory->create();
        $this->assertInstanceOf(BillingFake::class, $billing);

        unset($_ENV['HYVOR_FAKE']);
    }

    public function test_real(): void
    {
        $billingFactory = $this->container->get(BillingFactory::class);
        $this->assertInstanceOf(BillingFactory::class, $billingFactory);

        $billing = $billingFactory->create();
        $this->assertInstanceOf(Billing::class, $billing);
    }

}