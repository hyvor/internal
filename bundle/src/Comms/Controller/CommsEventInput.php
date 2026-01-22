<?php

namespace Hyvor\Internal\Bundle\Comms\Controller;

use Symfony\Component\Validator\Constraints as Assert;

class CommsEventInput
{

    #[Assert\NotBlank]
    public int $at;

    #[Assert\NotBlank]
    public string $event;

}