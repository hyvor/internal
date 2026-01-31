<?php

namespace Hyvor\Internal\Bundle\Comms\Async;

use Hyvor\Internal\Bundle\Comms\CommsInterface;
use Hyvor\Internal\Bundle\Comms\Exception\CommsApiFailedException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class AsyncMessageHandler
{

    public function __construct(
        private CommsInterface $comms
    ) {}

    /**
     * @throws CommsApiFailedException
     */
    public function __invoke(AsyncMessage $message): void
    {
        $this->comms->send($message->getEvent(), $message->getTo());
    }

}
