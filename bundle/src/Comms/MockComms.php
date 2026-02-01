<?php

namespace Hyvor\Internal\Bundle\Comms;

use Hyvor\Internal\Bundle\Comms\Event\AbstractEvent;
use Hyvor\Internal\Component\Component;
use PHPUnit\Framework\Assert;

class MockComms extends Comms {

    /**
     * @var array{event: AbstractEvent, to: Component, async: bool}[]
     */
    private array $sent = [];

    /**
     * @var array<class-string<AbstractEvent>, object|callable>
     */
    private array $responses = [];

    public function send(AbstractEvent $event, ?Component $to = null,): object|null
    {
        $to = $this->validateAndGetTo($event, $to);

        $this->sent[] = [
            'event' => $event,
            'to' => $to,
            'async' => false,
        ];

        $response = $this->responses[get_class($event)] ?? null;

        if (is_callable($response)) {
            return $response($event, $to);
        }

        // @phpstan-ignore-next-line
        return $response;
    }

    public function sendAsync(AbstractEvent $event, ?Component $to = null, string $transport = 'async'): void
    {
        $to = $this->validateAndGetTo($event, $to);

        $this->sent[] = [
            'event' => $event,
            'to' => $to,
            'async' => true,
        ];
    }

    /**
     * @param class-string<AbstractEvent> $eventClass
     */
    public function addResponse(string $eventClass, object|callable $response): void
    {
        $this->responses[$eventClass] = $response;
    }

    /**
     * @return array{event: AbstractEvent, to: Component, async: bool}[]
     */
    public function getSents(): array
    {
        return $this->sent;
    }

    /**
     * @template T of AbstractEvent
     * @param class-string<T> $class
     * @param null|(callable(T): void) $eventValidator
     */
    public function assertSent(
        string $class,
        Component $to,
        bool $async = false,
        ?callable $eventValidator = null
    ): void
    {
        foreach ($this->sent as $sent) {
            if (get_class($sent['event']) === $class && $sent['to'] === $to) {

                Assert::assertSame(
                    $async,
                    $sent['async'], "Expected event of class {$class} to be sent " .
                    ($async ? "async" : "sync") . " to {$to->value}, but it was sent " .
                    ($sent['async'] ? "async" : "sync") . "."
                );

                if ($eventValidator !== null) {
                    /** @var T $event */
                    $event = $sent['event'];
                    $eventValidator($event);
                }

                return;
            }
        }

        Assert::fail("Expected event of class {$class} to be sent to {$to->value}, but it was not.");
    }

    /**
     * @param class-string<AbstractEvent> $class
     */
    public function assertNotSent(string $class, ?Component $to): void
    {
        foreach ($this->sent as $sent) {
            if (get_class($sent['event']) === $class && ($to === null || $sent['to'] === $to)) {
                $toPart = $to ? " to {$to->value}" : "";
                Assert::fail("Expected event of class {$class}{$toPart} to not be sent, but it was.");
            }
        }
    }

}
