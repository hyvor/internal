<?php

namespace Hyvor\Internal\Auth\Event;

use Hyvor\Internal\Auth\AuthUser;
use Symfony\Component\DependencyInjection\Attribute\Exclude;


#[Exclude]
readonly class UserSignedUpEvent
{

    public function __construct(private AuthUser $user)
    {
    }

    public function getUser(): AuthUser
    {
        return $this->user;
    }

}