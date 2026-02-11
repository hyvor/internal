<?php

namespace Hyvor\Internal\Bundle\Comms\Ping;

use Hyvor\Internal\Bundle\Comms\Event\AbstractEvent;

/**
 * @codeCoverageIgnore
 */
class PingEvent extends AbstractEvent
{

    public function from(): array
    {
        return [];
    }

    public function to(): array
    {
        return [];
    }
}
