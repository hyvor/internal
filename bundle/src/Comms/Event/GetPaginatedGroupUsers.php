<?php

namespace Hyvor\Internal\Bundle\Comms\Event;

use Hyvor\Internal\Component\Component;

class GetPaginatedGroupUsers implements AbstractEvent
{

    public function from(): array
    {
        return [];
    }

    public function to(): array
    {
        return [Component::CORE];
    }
}