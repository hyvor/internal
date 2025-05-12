<?php

namespace Hyvor\Internal\Tests\Unit\Auth;

use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Tests\SymfonyTestCase;

class AuthFakeSymfonyTest extends SymfonyTestCase
{
    use AuthFakeTestTrait;

    /**
     * @param array<mixed> $user
     */
    protected function enable(?array $user = null): void
    {
        AuthFake::enableForSymfony($this->container, $user);
    }

}