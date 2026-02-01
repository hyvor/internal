<?php

namespace Hyvor\Internal\Tests\Unit\Billing;

use Hyvor\Internal\Billing\Billing;
use Hyvor\Internal\Billing\SubscriptionIntent;
use Hyvor\Internal\Bundle\Comms\CommsInterface;
use Hyvor\Internal\Bundle\Comms\MockComms;
use Hyvor\Internal\InternalApi\InternalApi;
use Hyvor\Internal\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Billing::class)]
#[CoversClass(SubscriptionIntent::class)]
class BillingLaravelTest extends LaravelTestCase
{

    use BillingTestTrait;

    public function setComms(MockComms $comms): void
    {
        app()->singleton(CommsInterface::class, fn() => $comms);
    }

    protected function getBilling(): Billing
    {
        return app(Billing::class);
    }

    protected function getInternalApi(): InternalApi
    {
        return app(InternalApi::class);
    }
}
