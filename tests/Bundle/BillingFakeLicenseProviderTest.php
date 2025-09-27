<?php

namespace Bundle;

use Hyvor\Internal\Billing\Dto\LicenseOf;
use Hyvor\Internal\Bundle\BillingFakeLicenseProvider;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\InternalFake;
use PHPUnit\Framework\TestCase;

class BillingFakeLicenseProviderTest extends TestCase
{

    public function test_test(): void
    {

        $fake = new BillingFakeLicenseProvider(InternalFake::class);
        $license = $fake->license(1, null, Component::BLOGS);
        $this->assertNotNull($license);

        $licenses = $fake->licenses([new LicenseOf(1)], Component::BLOGS);
        $this->assertCount(1, $licenses->all());

    }

}
