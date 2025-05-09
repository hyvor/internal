<?php

namespace Hyvor\Internal\Tests\Helper\Symfony;

use Hyvor\Internal\InternalConfig;
use Symfony\Component\DependencyInjection\Container;

/**
 * @deprecated Use UpdatesInternalConfig trait instead (works for both symfony and laravel)
 */
class InternalConfigTestHelper
{

    private static function updateObjectProperty(
        object $object,
        string $property,
        mixed $value
    ): void {
        $reflection = new \ReflectionObject($object);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    public static function updateSymfony(
        Container $container,
        string $key,
        mixed $value
    ): void {
        $currentConfig = $container->get(InternalConfig::class);
        assert($currentConfig instanceof InternalConfig);
        self::updateObjectProperty($currentConfig, $key, $value);
    }

    public static function updateLaravel(string $key, mixed $value): void
    {
        $currentConfig = app(InternalConfig::class);
        self::updateObjectProperty($currentConfig, $key, $value);
    }

}