<?php

namespace Hyvor\Internal\Tests\Unit\Auth;

use Hyvor\Internal\Auth\AuthMethod;
use PHPUnit\Framework\TestCase;

class AuthMethodTest extends TestCase
{

    public function test_is_oidc(): void
    {
        $authMethod = AuthMethod::OIDC;
        $this->assertTrue($authMethod->isOidc());

        $authMethod = AuthMethod::HYVOR;
        $this->assertFalse($authMethod->isOidc());
    }

}