<?php

namespace Hyvor\Internal\Tests\Unit\Http\Middleware;

use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Http\Exceptions\HttpException;
use Hyvor\Internal\Http\Middleware\AccessAuthUser;
use Hyvor\Internal\Http\Middleware\AuthMiddleware;
use Hyvor\Internal\Tests\LaravelTestCase;
use Illuminate\Http\Request;

class AuthMiddlewareLaravelTest extends LaravelTestCase
{

    public function testThrowsErrorWhenUserNotLoggedIn(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Unauthorized');

        AuthFake::enable(null);
        $request = new Request();
        (new AuthMiddleware())->handle($request, function () {
        });
    }

    public function testSetsAccessAuthUserWhenUserLoggedIn(): void
    {
        AuthFake::enable(['id' => 15]);

        $request = new Request();
        (new AuthMiddleware())->handle($request, function () {
            $user = app(AccessAuthUser::class);
            $this->assertInstanceOf(AccessAuthUser::class, $user);
            $this->assertEquals(15, $user->id);
        });
    }

}
