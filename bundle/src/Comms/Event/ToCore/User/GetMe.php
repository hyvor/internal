<?php

namespace Hyvor\Internal\Bundle\Comms\Event\ToCore\User;

use Hyvor\Internal\Bundle\Comms\Event\AbstractEvent;
use Hyvor\Internal\Component\Component;

/**
 * @extends AbstractEvent<GetMeResponse>
 */
class GetMe extends AbstractEvent
{

    public function __construct(private string $cookie) {}

    public function getCookie(): string
    {
        return $this->cookie;
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
