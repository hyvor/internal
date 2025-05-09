<?php

namespace Hyvor\Internal\Tests\Unit\Component;

use Hyvor\Internal\Component\InstanceUrlResolver;
use Hyvor\Internal\Tests\SymfonyTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(InstanceUrlResolver::class)]
class InstanceUrlResolverSymfonyTest extends SymfonyTestCase
{

    use InstanceUrlResolverTestTrait;

    protected function getInstanceUrlResolver(): InstanceUrlResolver
    {
        $resolver = $this->container->get(InstanceUrlResolver::class);
        assert($resolver instanceof InstanceUrlResolver);
        return $resolver;
    }
}