<?php

namespace Hyvor\Internal\Tests\Helper\Symfony;

use Hyvor\Internal\InternalConfig;
use Symfony\Component\DependencyInjection\Container;

class InternalConfigTestHelper
{

    /**
     * @return array<string, scalar>
     */
    public static function toArray(InternalConfig $config): array
    {
        $reflection = new \ReflectionClass($config);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PRIVATE);
        $data = [];

        foreach ($properties as $property) {
            $data[$property->getName()] = $property->getValue($config);
        }

        return $data;
    }

    /**
     * @param array<string, scalar> $data
     */
    public static function fromArray(array $data): InternalConfig
    {
        $reflection = new \ReflectionClass(InternalConfig::class);

        $instance = $reflection->newInstanceWithoutConstructor();
        foreach ($data as $key => $value) {
            if ($reflection->hasProperty($key)) {
                $property = $reflection->getProperty($key);
                $property->setValue($instance, $value);
            }
        }

        return $instance;
    }

    public static function withUpdatedProperty(InternalConfig $currentConfig, string $key, mixed $value): InternalConfig
    {
        $currentConfigArray = self::toArray($currentConfig);
        $currentConfigArray[$key] = $value;
        return self::fromArray($currentConfigArray);
    }

    public static function setContainerWithUpdatedProperty(
        Container $container,
        string $key,
        mixed $value
    ): void {
        $currentConfig = $container->get(InternalConfig::class);
        assert($currentConfig instanceof InternalConfig);
        $updated = self::withUpdatedProperty($currentConfig, $key, $value);
        $container->set(InternalConfig::class, $updated);
    }


}