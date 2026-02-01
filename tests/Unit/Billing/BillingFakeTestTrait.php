<?php

namespace Hyvor\Internal\Tests\Unit\Billing;

use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\BillingInterface;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicenseType;
use Hyvor\Internal\Component\Component;
use PHPUnit\Framework\Attributes\TestWith;

trait BillingFakeTestTrait
{

    /**
     * @param array<int, ResolvedLicense>|callable|null $licenses
     */
    protected abstract function enable(
        ?BlogsLicense $license = null,
        array|callable|null $licenses = null
    ): void;

    public function testEnableAndLicense(): void
    {
        $this->enable(license: BlogsLicense::trial());
        $fake = $this->getContainer()->get(BillingInterface::class);
        $this->assertInstanceOf(BillingFake::class, $fake);
        $this->assertInstanceOf(BlogsLicense::class, $fake->license(1));
    }

    public function testEnableAndLicenseNull(): void
    {
        $this->enable(null);
        $fake = $this->getContainer()->get(BillingInterface::class);
        $this->assertInstanceOf(BillingFake::class, $fake);

        $license = $fake->license(1);
        // TODO:
    }

    #[TestWith([true])]
    #[TestWith([false])]
    public function test_enable_and_licenses(bool $callback): void
    {
        $license1 = BlogsLicense::trial();
        $license2 = BlogsLicense::trial();
        $license2->users = 5;

        $data = [
            1 => new ResolvedLicense(ResolvedLicenseType::SUBSCRIPTION, $license1),
            2 => new ResolvedLicense(ResolvedLicenseType::ENTERPRISE_CONTRACT, $license2),
            3 => new ResolvedLicense(ResolvedLicenseType::NONE)
        ];

        $this->enable(licenses: $callback ? fn() => $data : $data);
        $fake = $this->getContainer()->get(BillingInterface::class);
        $this->assertInstanceOf(BillingFake::class, $fake);

        $allLicenses = $fake->licenses([1, 2, 3]);

        $this->assertCount(3, $allLicenses);

        $license1 = $allLicenses[1];
        $this->assertInstanceOf(BlogsLicense::class, $license1);
        $this->assertEquals(2, $license1->users);

        $license2 = $allLicenses[2];
        $this->assertInstanceOf(BlogsLicense::class, $license2);
        $this->assertEquals(5, $license2->users);

        $license3 = $allLicenses[3];
        // TODO:
    }

}
