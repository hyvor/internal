<?php

namespace Hyvor\Internal\Bundle\Testing\Scheduler;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Scheduler\Generator\MessageContext;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Component\Scheduler\Trigger\TriggerInterface;

trait SchedulerTestingTrait
{

    /**
     * @template T of object
     * @param class-string<T> $typeClass
     * @return array<T>
     */
    protected function getMessagesOfType(ScheduleProviderInterface $scheduleProvider, string $typeClass): array
    {
        assert($this instanceof TestCase);

        $messages = $scheduleProvider->getSchedule()->getRecurringMessages();
        $filtered = [];
        $messageContext = new MessageContext(
            '',
            '',
            $this->createMock(TriggerInterface::class),
            new \DateTimeImmutable()
        );
        foreach ($messages as $message) {
            foreach ($message->getMessages($messageContext) as $msg) {
                if ($msg instanceof $typeClass) {
                    $filtered[] = $msg;
                }
            }
        }
        return $filtered;
    }

}