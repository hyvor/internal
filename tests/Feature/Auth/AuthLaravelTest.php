<?php

namespace Hyvor\Internal\Tests\Feature\Auth;

use Hyvor\Internal\Auth\Auth;
use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Tests\LaravelTestCase;
use Illuminate\Http\RedirectResponse;

class AuthLaravelTest extends LaravelTestCase
{

    private function getAuth(): Auth
    {
        return app(Auth::class);
    }

    public function testChecks(): void
    {
        AuthFake::enable(['id' => 1]);
        $user = $this->getAuth()->check();
        $this->assertNotFalse($user);
        $this->assertEquals(1, $user->id);

        AuthFake::enable(['id' => 2]);
        $user = $this->getAuth()->check();
        $this->assertNotFalse($user);
        $this->assertEquals(2, $user->id);

        AuthFake::enable(null);
        $this->assertFalse($this->getAuth()->check());
    }

    public function testRedirects(): void
    {
        $login = $this->getAuth()->login();
        $this->assertInstanceOf(RedirectResponse::class, $login);
        $this->assertStringStartsWith('https://hyvor.com/login?redirect=', $login->getTargetUrl());

        $signup = $this->getAuth()->signup();
        $this->assertInstanceOf(RedirectResponse::class, $signup);
        $this->assertStringStartsWith('https://hyvor.com/signup?redirect=', $signup->getTargetUrl());

        $logout = $this->getAuth()->logout();
        $this->assertInstanceOf(RedirectResponse::class, $logout);
        $this->assertStringStartsWith('https://hyvor.com/logout?redirect=', $logout->getTargetUrl());
    }

}
