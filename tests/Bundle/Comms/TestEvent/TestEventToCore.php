<?php

namespace Hyvor\Internal\Tests\Bundle\Comms\TestEvent;

use Hyvor\Internal\Bundle\Comms\Event\AbstractEvent;
use Hyvor\Internal\Component\Component;

/**
 * @extends AbstractEvent<object>
 */
class TestEventToCore extends AbstractEvent
{

    public function __construct(
        public int $id
    ) {
    }

    public function from(): array
    {
        return [];
    }

    public function to(): array
    {
        return [Component::CORE];
    }

}