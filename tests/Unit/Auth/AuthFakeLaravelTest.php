<?php

namespace Hyvor\Internal\Tests\Unit\Auth;

use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Tests\LaravelTestCase;

class AuthFakeLaravelTest extends LaravelTestCase
{
    use AuthFakeTestTrait;

    /**
     * @param array<mixed> $user
     */
    protected function enable(?array $user = null): void
    {
        AuthFake::enable($user);
    }
}
