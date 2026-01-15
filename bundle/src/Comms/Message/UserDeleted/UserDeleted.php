<?php

namespace Hyvor\Internal\Bundle\Comms\Message\UserDeleted;

class UserDeleted
{
    public function __construct(public int $userId)
    {
    }
}