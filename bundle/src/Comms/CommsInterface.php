<?php

namespace Hyvor\Internal\Bundle\Comms;

use Hyvor\Internal\Bundle\Comms\Event\AbstractEvent;
use Hyvor\Internal\Bundle\Comms\Exception\CommsApiFailedException;
use Hyvor\Internal\Component\Component;

interface CommsInterface {

    public function signature(string $content): string;

    /**
     * @template TResponse of object|null
     * @template T of AbstractEvent<TResponse>
     * @param T $event
     * @param Component|null $to if null, the message's to() method MUST return exactly one component, which will be used
     * @return TResponse
     * @throws CommsApiFailedException
     */
    public function send(
        AbstractEvent $event,
        ?Component $to = null,
    ): object|null;

    public function sendAsync(AbstractEvent $event, ?Component $to = null, string $transport = 'async'): void;

}
