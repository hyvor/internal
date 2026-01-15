<?php

namespace Hyvor\Internal\Bundle\Comms\Message;

use Hyvor\Internal\Component\Component;

class GetPaginatedGroupUsers implements MessageInterface
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