<?php

namespace Hyvor\Internal\Tests\Unit\Auth;

use Hyvor\Internal\Auth\Auth;
use Hyvor\Internal\Bundle\Comms\CommsInterface;
use Hyvor\Internal\Bundle\Comms\MockComms;
use Hyvor\Internal\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Auth::class)]
class AuthLaravelTest extends LaravelTestCase
{
    use AuthTestTrait;

    public function setComms(MockComms $comms): void
    {
        app()->singleton(CommsInterface::class, fn() => $comms);
    }
}
