<?php

namespace Hyvor\Internal\Tests\Unit\Billing\Usage;

use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\Usage\UsageAbstract;

/**
 * @extends UsageAbstract<BlogsLicense>
 */
class ExampleStorageUsage extends UsageAbstract
{

    public function getLicenseType(): string
    {
        return BlogsLicense::class;
    }

    public function getKey(): string
    {
        return 'storage';
    }

    public function usageOfResource(int $resourceId): int
    {
        return 100;
    }

    public function usageOfOrganization(int $userId): int
    {
        return 200;
    }
}
