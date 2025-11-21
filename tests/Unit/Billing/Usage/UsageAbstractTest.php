<?php

namespace Hyvor\Internal\Tests\Unit\Billing\Usage;

use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\License\DerivedFrom;
use Hyvor\Internal\Billing\Usage\UsageAbstract;
use Hyvor\Internal\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UsageAbstract::class)]
class UsageAbstractTest extends LaravelTestCase
{

    public function testExampleStorageUsage(): void
    {
        $usage = new ExampleStorageUsage();
        $this->assertEquals(100, $usage->usageOfResource(1));
        $this->assertEquals(200, $usage->usageOfOrganization(1));

        // user
        $licenseWithoutDerived = new BlogsLicense(storage: 300);
        $this->assertFalse($usage->hasReached($licenseWithoutDerived, 1, 1));

        // resource
        $resourceLicense = new BlogsLicense(storage: 100)->setDerivedFrom(DerivedFrom::CUSTOM_RESOURCE);
        $this->assertTrue($usage->hasReached($resourceLicense, 1, 1));
        $this->assertFalse($usage->hasExceeded($resourceLicense, 1, 1));
    }

}
