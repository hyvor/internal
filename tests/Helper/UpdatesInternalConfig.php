<?php

namespace Hyvor\Internal\Tests\Helper;

use Hyvor\Internal\InternalConfig;
use Hyvor\Internal\Tests\LaravelTestCase;
use Hyvor\Internal\Tests\SymfonyTestCase;

trait UpdatesInternalConfig
{

    private function getThis(): object
    {
        return $this;
    }

    public function updateInternalConfig(string $key, mixed $value): void
    {
        $instance = $this->getThis();

        if ($instance instanceof LaravelTestCase) {
            $config = app(InternalConfig::class);
            $this->updateObjectProperty($config, $key, $value);
        } elseif ($instance instanceof SymfonyTestCase) {
            $config = $instance->container->get(InternalConfig::class);
            assert($config instanceof InternalConfig);
            $this->updateObjectProperty($config, $key, $value);
        } else {
            throw new \RuntimeException('Unknown test case type');
        }
    }

    private function updateObjectProperty(
        object $object,
        string $property,
        mixed $value
    ): void {
        $reflection = new \ReflectionObject($object);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

}