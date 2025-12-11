<?php

namespace Hyvor\Internal\Bundle\Testing;

use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Bundle\EventDispatcher\TestEventDispatcher;
use Monolog\Handler\TestHandler;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

trait BaseTestingTrait
{

    use InteractsWithMessenger;
    use Factories;

    /**
     * @template T of object
     * @param class-string<T> $serviceId
     * @return T
     */
    public function getService(string $serviceId): mixed
    {
        /** @var T $service */
        $service = $this->container->get($serviceId);
        return $service;
    }


    public function getTestLogger(): TestHandler
    {
        $logger = $this->container->get('monolog.handler.test');
        $this->assertInstanceOf(TestHandler::class, $logger);
        return $logger;
    }

    protected function getMessageBus(): MessageBusInterface
    {
        $bus = $this->container->get('messenger.default_bus');
        $this->assertInstanceOf(MessageBusInterface::class, $bus);
        return $bus;
    }

    public function getEm(): EntityManagerInterface
    {
        return $this->getService(EntityManagerInterface::class);
    }

    public function getEd(): TestEventDispatcher
    {
        $ed = $this->getService(EventDispatcherInterface::class);
        assert($ed instanceof TestEventDispatcher);
        return $ed;
    }

}