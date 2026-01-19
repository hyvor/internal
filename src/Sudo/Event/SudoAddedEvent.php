<?php

namespace Hyvor\Internal\Sudo\Event;

use Hyvor\Internal\Bundle\Entity\SudoUser;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
readonly class SudoAddedEvent
{

    public function __construct(private SudoUser $sudoUser)
    {
    }

    public function getSudoUser(): SudoUser
    {
        return $this->sudoUser;
    }

}
