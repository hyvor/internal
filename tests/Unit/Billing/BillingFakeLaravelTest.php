<?php

namespace Hyvor\Internal\Tests\Unit\Billing;

use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(BillingFake::class)]
class BillingFakeLaravelTest extends LaravelTestCase
{

    use BillingFakeTestTrait;

    protected function enable(?BlogsLicense $license = null, array|callable|null $licenses = null): void
    {
        BillingFake::enable(license: $license, licenses: $licenses);
    }

}
