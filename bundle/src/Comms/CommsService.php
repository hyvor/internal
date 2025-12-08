<?php

namespace Hyvor\Internal\Bundle\Comms;

use Hyvor\Internal\Bundle\Comms\Message\MessageInterface;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\InternalConfig;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CommsService
{

    public function __construct(
        private HttpClientInterface $httpClient,
        private InternalConfig $internalConfig,
    ) {
    }

    /**
     * @param Component|null $to if null, the message's to() method MUST return exactly one component, which will be used
     * @param Component|null $from the current component will be used if null
     */
    public function send(
        MessageInterface $message,
        ?Component $to = null,
        ?Component $from = null,
    ): void {
        $allowedFrom = $message->from();
        $allowedTo = $message->to();

        if ($to === null) {
            if (count($allowedTo) !== 1) {
                throw new \InvalidArgumentException('Message to() must return exactly one component when $to is null');
            }
            $to = $allowedTo[0];
        }

        if ($from === null) {
            $from = $this->internalConfig->getComponent();
        }

        if (!empty($allowedFrom) && !in_array($from, $allowedFrom, true)) {
            throw new \InvalidArgumentException("Message cannot be sent from component {$from->value}");
        }

        if (!empty($allowedTo) && !in_array($to, $allowedTo, true)) {
            throw new \InvalidArgumentException("Message cannot be sent to component {$to->value}");
        }
    }

}