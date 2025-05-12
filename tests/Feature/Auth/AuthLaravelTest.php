<?php

namespace Hyvor\Internal\Tests\Feature\Auth;

use Hyvor\Internal\Auth\Auth;
use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @deprecated covered in Unit\Auth\AuthLaravelTest
 */
#[CoversClass(Auth::class)]
class AuthLaravelTest extends LaravelTestCase
{

    private function getAuth(): AuthInterface
    {
        return app(Auth::class);
    }

    public function test_login_check(): void
    {
        AuthFake::enable(['id' => 1]);
        $user = $this->getAuth()->check('');
        $this->assertNotFalse($user);
        $this->assertEquals(1, $user->id);

        AuthFake::enable(['id' => 2]);
        $user = $this->getAuth()->check('');
        $this->assertNotFalse($user);
        $this->assertEquals(2, $user->id);

        AuthFake::enable(null);
        $this->assertFalse($this->getAuth()->check(''));
    }

    public function test_redirects(): void
    {
        $auth = $this->getAuth();
        assert($auth instanceof Auth);

        $login = $auth->login();
        $this->assertStringStartsWith('https://hyvor.com/login?redirect=', $login->getTargetUrl());

        $signup = $auth->signup();
        $this->assertStringStartsWith('https://hyvor.com/signup?redirect=', $signup->getTargetUrl());

        $logout = $auth->logout();
        $this->assertStringStartsWith('https://hyvor.com/logout?redirect=', $logout->getTargetUrl());
    }

}
