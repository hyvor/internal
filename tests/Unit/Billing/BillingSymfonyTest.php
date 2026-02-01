<?php

namespace Hyvor\Internal\Tests\Unit\Billing;

use Hyvor\Internal\Billing\Billing;
use Hyvor\Internal\Billing\SubscriptionIntent;
use Hyvor\Internal\Bundle\Comms\MockComms;
use Hyvor\Internal\InternalApi\InternalApi;
use Hyvor\Internal\Tests\SymfonyTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Billing::class)]
#[CoversClass(SubscriptionIntent::class)]
class BillingSymfonyTest extends SymfonyTestCase
{

    use BillingTestTrait;

    function setComms(MockComms $comms): void
    {
        return; // already set
    }

    protected function getBilling(): Billing
    {
        $billing = $this->container->get(Billing::class);
        assert($billing instanceof Billing);
        return $billing;
    }

    protected function getInternalApi(): InternalApi
    {
        $internalApi = $this->container->get(InternalApi::class);
        assert($internalApi instanceof InternalApi);
        return $internalApi;
    }
}
