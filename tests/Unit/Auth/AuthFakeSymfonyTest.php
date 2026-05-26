<?php

namespace Hyvor\Internal\Tests\Unit\Auth;

use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Tests\SymfonyTestCase;

class AuthFakeSymfonyTest extends SymfonyTestCase
{
    use AuthFakeTestTrait;

    /**
     * @param array<mixed>|null $user
     * @param array<mixed>|null $organizationsDatabase
     */
    protected function enable(?array $user = null, ?array $organizationsDatabase = null): void
    {
        AuthFake::enableForSymfony($this->container, $user, organizationsDatabase: $organizationsDatabase);
    }

}