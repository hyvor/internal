<?php

namespace Hyvor\Internal\Bundle\Testing;

use PHPUnit\Framework\Assert;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TestEventDispatcher extends EventDispatcher
{

    /** @var object[] */
    private array $dispatchedEvents = [];

    /**
     * @param false|string[] $mockEvents false to not mock any events or an array of event names to mock.
     *                                   Listeners of those events will not be called when the event is dispatched.
     *                                   Useful to prevent side effects.
     */
    public function __construct(
        private false|array $mockEvents = false,
    ) {
        parent::__construct();
    }

    public function dispatch(object $event, ?string $eventName = null): object
    {
        $eventName ??= $event::class;
        $this->dispatchedEvents[] = $event;

        if ($this->mockEvents !== false && in_array($eventName, $this->mockEvents, true)) {
            // If the event is mocked, we do not call the parent dispatch method.
            // This allows us to mock events without triggering actual listeners.
            // Helpful when the listeners cause side effects.
            return $event;
        }

        return parent::dispatch($event, $eventName);
    }

    /**
     * @return object[]
     */
    public function getDispatchedEvents(): array
    {
        return $this->dispatchedEvents;
    }

    /**
     * Gets the first event. Throws an error if the event is not found.
     *
     * @template T of object
     * @param class-string<T> $eventName
     * @return T
     */
    public function getFirstEvent(string $eventName): object
    {
        /** @var T|null $val */
        $val = array_find($this->dispatchedEvents, fn($event) => $event::class === $eventName);

        Assert::assertNotNull(
            $val,
            "Event '$eventName' was not dispatched."
        );

        return $val;
    }

    public function assertDispatched(string $eventName): void
    {
        $this->assertDispatchedCount($eventName, 1);
    }

    public function assertNotDispatched(string $eventName): void
    {
        Assert::assertFalse(
            in_array($eventName, array_map(fn($event) => $event::class, $this->dispatchedEvents), true),
            "Event '$eventName' was dispatched, but it should not have been."
        );
    }

    public function assertDispatchedCount(string $eventName, int $count): void
    {
        $actualCount = count(array_filter($this->dispatchedEvents, fn($event) => $event::class === $eventName));
        Assert::assertSame(
            $count,
            $actualCount,
            "Event '$eventName' was dispatched $actualCount times, expected $count."
        );
    }

    /**
     * @param false|string[] $mockEvents false to not mock any events or an array of event names to mock.
     *                                   Listeners of those events will not be called when the event is dispatched.
     *                                   Useful to prevent side effects.
     */
    public static function enable(Container $container, false|array $mockEvents = false): self
    {
        $dispatcher = new self($mockEvents);
        $container->set(
            EventDispatcherInterface::class,
            $dispatcher
        );
        return $dispatcher;
    }

}