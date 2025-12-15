<?php

namespace Hyvor\Internal\Bundle\Comms\Event\UserDeleted;

class UserDeleted
{
    public function __construct(public int $userId)
    {
    }
}