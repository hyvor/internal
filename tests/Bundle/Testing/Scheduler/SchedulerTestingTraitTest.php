<?php

namespace Hyvor\Internal\Tests\Bundle\Testing\Scheduler;

use Hyvor\Internal\Bundle\Testing\Scheduler\SchedulerTestingTrait;
use Hyvor\Internal\Tests\SymfonyTestCase;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

class SchedulerTestingTraitTest extends SymfonyTestCase
{

    use SchedulerTestingTrait;

    public function test_get_messages_of_type(): void
    {
        $scheduleProvider = new TestScheduleProvider();
        $messages = $this->getMessagesOfType($scheduleProvider, TestMessage::class);

        $this->assertCount(1, $messages);
        $this->assertInstanceOf(TestMessage::class, $messages[0]);
    }

}

class TestScheduleProvider implements ScheduleProviderInterface
{

    public function getSchedule(): Schedule
    {
        return (new Schedule())
            ->add(RecurringMessage::every('1 hour', new TestMessage));
    }
}

class TestMessage
{
}
