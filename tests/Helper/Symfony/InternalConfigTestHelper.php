<?php

namespace Hyvor\Internal\Tests\Helper\Symfony;

use Hyvor\Internal\InternalConfig;
use Symfony\Component\DependencyInjection\Container;

class InternalConfigTestHelper
{

    public static function setContainerWithUpdatedProperty(
        Container $container,
        string $key,
        mixed $value
    ): void {
        $currentConfig = $container->get(InternalConfig::class);
        assert($currentConfig instanceof InternalConfig);
        $reflection = new \ReflectionObject($currentConfig);
        $property = $reflection->getProperty($key);
        $property->setAccessible(true);
        $property->setValue($currentConfig, $value);
    }


}