<?php

namespace Hyvor\Internal\Tests\Unit\Billing;

use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\BillingInterface;
use Hyvor\Internal\Billing\Dto\LicenseOf;
use Hyvor\Internal\Billing\Dto\LicensesCollection;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Component\Component;
use PHPUnit\Framework\Attributes\TestWith;

trait BillingFakeTestTrait
{

    protected abstract function enable(
        ?BlogsLicense $license = null,
        LicensesCollection|callable|null $licenses = null
    ): void;

    public function testEnableAndLicense(): void
    {
        $this->enable(license: new BlogsLicense);
        $fake = $this->getContainer()->get(BillingInterface::class);
        $this->assertInstanceOf(BillingFake::class, $fake);
        $this->assertInstanceOf(BlogsLicense::class, $fake->license(1, 1));
    }

    public function testEnableAndLicenseNull(): void
    {
        $this->enable(null);
        $fake = $this->getContainer()->get(BillingInterface::class);
        $this->assertInstanceOf(BillingFake::class, $fake);
        $this->assertNull($fake->license(1, 1));
    }

    #[TestWith([true])]
    #[TestWith([false])]
    public function test_enable_and_licenses(bool $callback): void
    {
        $license1 = new BlogsLicense;
        $license2 = new BlogsLicense(users: 5);

        $collection = new LicensesCollection([
            [
                'user_id' => 1,
                'resource_id' => 1,
                'license' => $license1->serialize()
            ],
            [
                'user_id' => 2,
                'resource_id' => 2,
                'license' => $license2->serialize()
            ],
            [
                'user_id' => 3,
                'resource_id' => null,
                'license' => null
            ]
        ], Component::BLOGS);

        $this->enable(licenses: $callback ? fn() => $collection : $collection);
        $fake = $this->getContainer()->get(BillingInterface::class);
        $this->assertInstanceOf(BillingFake::class, $fake);

        $allLicenses = $fake->licenses([
            new LicenseOf(1, 1),
            new LicenseOf(2, 2),
            new LicenseOf(3, null)
        ]);

        $this->assertCount(3, $allLicenses->all());

        $license1 = $allLicenses->of(1, 1);
        $this->assertInstanceOf(BlogsLicense::class, $license1);
        $this->assertEquals(2, $license1->users);

        $license2 = $allLicenses->of(2, 2);
        $this->assertInstanceOf(BlogsLicense::class, $license2);
        $this->assertEquals(5, $license2->users);

        $license3 = $allLicenses->of(3, null);
        $this->assertNull($license3);
    }

}
