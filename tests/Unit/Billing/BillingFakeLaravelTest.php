<?php

namespace Hyvor\Internal\Tests\Unit\Billing;

use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(BillingFake::class)]
class BillingFakeLaravelTest extends LaravelTestCase
{

    use BillingFakeTestTrait;

    protected function enable(array|callable $licenses = []): void
    {
        BillingFake::enable(licenses: $licenses);
    }

}
