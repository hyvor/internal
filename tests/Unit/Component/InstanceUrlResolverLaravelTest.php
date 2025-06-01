<?php

namespace Hyvor\Internal\Tests\Unit\Component;

use Hyvor\Internal\Component\InstanceUrlResolver;
use Hyvor\Internal\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(InstanceUrlResolver::class)]
class InstanceUrlResolverLaravelTest extends LaravelTestCase
{
    use InstanceUrlResolverTestTrait;

    protected function getInstanceUrlResolver(): InstanceUrlResolver
    {
        return app(InstanceUrlResolver::class);
    }
}