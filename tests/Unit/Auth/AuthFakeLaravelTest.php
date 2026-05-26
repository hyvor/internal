<?php

namespace Hyvor\Internal\Tests\Unit\Auth;

use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Tests\LaravelTestCase;

class AuthFakeLaravelTest extends LaravelTestCase
{
    use AuthFakeTestTrait;

    /**
     * @param array<mixed>|null $user
     * @param array<mixed>|null $organizationsDatabase
     */
    protected function enable(?array $user = null, ?array $organizationsDatabase = null): void
    {
        AuthFake::enable($user, organizationsDatabase: $organizationsDatabase);
    }
}
