<?php

namespace Hyvor\Internal\Tests\Unit\Billing;

use Hyvor\Internal\Billing\Billing;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\BillingInterface;
use Hyvor\Internal\Billing\Dto\LicenseOf;
use Hyvor\Internal\Billing\Dto\LicensesCollection;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;

#[CoversClass(BillingFake::class)]
class BillingFakeLaravelTest extends LaravelTestCase
{

    use BillingFakeTestTrait;

    protected function enable(?BlogsLicense $license = null, LicensesCollection|callable|null $licenses = null): void
    {
        BillingFake::enable(license: $license, licenses: $licenses);
    }

}
