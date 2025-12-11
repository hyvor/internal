<?php

namespace Hyvor\Internal\Tests\Bundle\Testing;

use Hyvor\Internal\Bundle\EventDispatcher\TestEventDispatcher;
use Hyvor\Internal\Tests\SymfonyTestCase;

class TestEventDispatcherTest extends SymfonyTestCase
{

    public function test_dispatcher(): void
    {
        $testDispatcher = TestEventDispatcher::enable($this->container);
        $testDispatcher->dispatch(new TestEvent('test', 10), 'TestEvent');

        $dispatchedEvents = $testDispatcher->getDispatchedEvents();
        $this->assertCount(1, $dispatchedEvents);
        $this->assertInstanceOf(TestEvent::class, $dispatchedEvents[0]);

        $event1 = $testDispatcher->getFirstEvent(TestEvent::class);
        $this->assertInstanceOf(TestEvent::class, $event1);

        $testDispatcher->assertDispatched(TestEvent::class);
        $testDispatcher->assertNotDispatched('TestEvent2');
        $testDispatcher->assertDispatchedCount(TestEvent::class, 1);
    }

    public function test_mocks(): void
    {
        $testDispatcher = TestEventDispatcher::enable($this->container, [TestDangerousEvent::class]);

        $testDispatcher->addListener(TestDangerousEvent::class, function (TestDangerousEvent $event) {
            // this is not called because TestDangerousEvent is mocked
            // TestEventDispatcher works kind of like a spy there
            dd('here');  // has a dangerous listener
        });

        $testDispatcher->dispatch(new TestDangerousEvent());

        $dispatchedEvents = $testDispatcher->getDispatchedEvents();
        $this->assertCount(1, $dispatchedEvents);
    }

}

class TestEvent
{
    public function __construct(
        public string $name,
        public int $value
    ) {
    }
}

class TestDangerousEvent
{
    public function __construct()
    {
    }
}