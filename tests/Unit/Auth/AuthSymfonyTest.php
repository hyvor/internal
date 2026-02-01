<?php

namespace Hyvor\Internal\Tests\Unit\Auth;

use Hyvor\Internal\Bundle\Comms\MockComms;
use Hyvor\Internal\Tests\SymfonyTestCase;

class AuthSymfonyTest extends SymfonyTestCase
{
    use AuthTestTrait;

    function setComms(MockComms $comms): void
    {
        return; // already set
    }
}
