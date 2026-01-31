<?php

namespace Hyvor\Internal\Bundle\Comms\Async;

use Hyvor\Internal\Bundle\Comms\Event\AbstractEvent;
use Hyvor\Internal\Component\Component;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage]
class AsyncMessage {

    public function __construct(
        private AbstractEvent $event,
        private Component $to
    ) {}

    public function getEvent(): AbstractEvent
    {
        return $this->event;
    }

    public function getTo(): Component
    {
        return $this->to;
    }

}
