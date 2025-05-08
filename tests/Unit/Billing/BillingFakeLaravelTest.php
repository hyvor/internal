<?php

namespace Hyvor\Internal\Tests\Unit\Billing;

use Hyvor\Internal\Billing\Billing;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(BillingFake::class)]
class BillingFakeLaravelTest extends LaravelTestCase
{

    public function testEnableAndLicense(): void
    {
        BillingFake::enable(license: new BlogsLicense);
        $fake = app(Billing::class);
        $this->assertInstanceOf(BillingFake::class, $fake);
        $this->assertInstanceOf(BlogsLicense::class, $fake->license(1, 1));

        BillingFake::enable(null);
        $fake = app(Billing::class);
        $this->assertInstanceOf(BillingFake::class, $fake);
        $this->assertNull($fake->license(1, 1));
    }

}
