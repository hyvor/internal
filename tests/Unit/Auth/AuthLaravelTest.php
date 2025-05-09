<?php

namespace Hyvor\Internal\Tests\Unit\Auth;

use Hyvor\Internal\Auth\Auth;
use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\InternalApi\InternalApi;
use Hyvor\Internal\Tests\LaravelTestCase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Auth::class)]
class AuthLaravelTest extends LaravelTestCase
{
    private Auth $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = app(Auth::class);
    }

    public function testCheckWhenNoCookieSet(): void
    {
        $this->assertFalse($this->provider->check(''));
    }

    public function testCheckWhenCookieIsSet(): void
    {
        $_COOKIE = [
            Auth::HYVOR_SESSION_COOKIE_NAME => 'test-cookie'
        ];

        Http::fake([
            'https://hyvor.com/api/internal/auth/check' => Http::response([
                'user' => [
                    'id' => 1,
                    'name' => 'test',
                    'username' => 'test',
                    'email' => 'test@test.com'
                ]
            ])
        ]);

        $user = $this->provider->check();

        $this->assertInstanceOf(AuthUser::class, $user);
        $this->assertEquals(1, $user->id);
        $this->assertEquals('test', $user->name);
        $this->assertEquals('test', $user->username);
        $this->assertEquals('test@test.com', $user->email);

        Http::assertSent(function (Request $request) {
            $data = InternalApi::dataFromMessage($request->data()['message']);
            $this->assertEquals('test-cookie', $data['cookie']);
            return true;
        });
    }

    public function testReturnsFalseWhenUserIsNull(): void
    {
        $_COOKIE = [
            Auth::HYVOR_SESSION_COOKIE_NAME => 'test'
        ];
        Http::fake([
            'https://hyvor.com/api/internal/auth/check' => Http::response([
                'user' => null
            ])
        ]);
        $this->assertFalse($this->provider->check());
    }

    public function testRedirects(): void
    {
        $login = $this->provider->login();
        $this->assertInstanceOf(RedirectResponse::class, $login);
        $this->assertStringStartsWith('https://hyvor.com/login?redirect=', $login->getTargetUrl());

        $signup = $this->provider->signup();
        $this->assertInstanceOf(RedirectResponse::class, $signup);
        $this->assertStringStartsWith('https://hyvor.com/signup?redirect=', $signup->getTargetUrl());

        $logout = $this->provider->logout();
        $this->assertInstanceOf(RedirectResponse::class, $logout);
        $this->assertStringStartsWith('https://hyvor.com/logout?redirect=', $logout->getTargetUrl());

        // page
        $login = $this->provider->login('/exit');
        $this->assertInstanceOf(RedirectResponse::class, $login);
        $this->assertStringStartsWith('https://hyvor.com/login?redirect=', $login->getTargetUrl());
        $this->assertStringContainsString('redirect=http%3A%2F%2Flocalhost%2Fexit', $login->getTargetUrl());

        // full URL
        $login = $this->provider->login('https://example.com/exit');
        $this->assertInstanceOf(RedirectResponse::class, $login);
        $this->assertStringStartsWith('https://hyvor.com/login?redirect=', $login->getTargetUrl());
        $this->assertStringContainsString('redirect=https%3A%2F%2Fexample.com%2Fexit', $login->getTargetUrl());
    }

    public function testFromIds(): void
    {
        Http::fake([
            'https://hyvor.com/api/internal/auth/users/from/ids' => Http::response([
                1 => [
                    'id' => 1,
                    'name' => 'test',
                    'username' => 'test',
                    'email' => 'test@hyvor.com'
                ],
                2 => [
                    'id' => 2,
                    'name' => 'test2',
                    'username' => 'test2',
                    'email' => 'test2@hyvor.com'
                ]
            ])
        ]);

        $users = $this->provider->fromIds([1, 2]);

        $this->assertInstanceOf(Collection::class, $users);
        $this->assertCount(2, $users);

        $this->assertInstanceOf(AuthUser::class, $users[1]);
        $this->assertEquals(1, $users[1]->id);
        $this->assertEquals('test', $users[1]->name);
        $this->assertEquals('test', $users[1]->username);
        $this->assertEquals('test@hyvor.com', $users[1]->email);

        $this->assertInstanceOf(AuthUser::class, $users[2]);
        $this->assertEquals(2, $users[2]->id);
        $this->assertEquals('test2', $users[2]->name);
        $this->assertEquals('test2', $users[2]->username);
        $this->assertEquals('test2@hyvor.com', $users[2]->email);

        Http::assertSent(function (Request $request) {
            $data = InternalApi::dataFromMessage($request->data()['message']);
            return $data['ids'] === [1, 2];
        });
    }

    public function testFromId(): void
    {
        Http::fake([
            'https://hyvor.com/api/internal/auth/users/from/ids' => Http::response([
                1 => [
                    'id' => 1,
                    'name' => 'test',
                    'username' => 'test',
                    'email' => 'test@hyvor.com',
                    'picture_url' => 'https://hyvor.com/avatar.png'
                ]
            ])
        ]);

        $user = $this->provider->fromId(1);

        $this->assertInstanceOf(AuthUser::class, $user);
        $this->assertEquals(1, $user->id);
        $this->assertEquals('test', $user->name);
        $this->assertEquals('test', $user->username);
        $this->assertEquals('test@hyvor.com', $user->email);
        $this->assertEquals('https://hyvor.com/avatar.png', $user->picture_url);

        Http::assertSent(function (Request $request) {
            $data = InternalApi::dataFromMessage($request->data()['message']);
            return $data['ids'] === [1];
        });
    }

    public function testFromIdNotFound(): void
    {
        Http::fake([
            'https://hyvor.com/api/internal/auth/users/from/ids' => Http::response([])
        ]);
        $user = $this->provider->fromId(1);
        $this->assertNull($user);
    }

    public function testFromUsernames(): void
    {
        Http::fake([
            'https://hyvor.com/api/internal/auth/users/from/usernames' => Http::response([
                'test' => [
                    'id' => 1,
                    'name' => 'test',
                    'username' => 'test',
                    'email' => 'test@hyvor.com',
                ],
                'test2' => [
                    'id' => 2,
                    'name' => 'test2',
                    'username' => 'test2',
                    'email' => 'test2@hyvor.com',
                ]
            ])
        ]);

        $users = $this->provider->fromUsernames(['test', 'test2']);

        $this->assertInstanceOf(Collection::class, $users);
        $this->assertCount(2, $users);

        $this->assertInstanceOf(AuthUser::class, $users['test']);
        $this->assertEquals(1, $users['test']->id);
        $this->assertEquals('test', $users['test']->name);
        $this->assertEquals('test', $users['test']->username);
        $this->assertEquals('test@hyvor.com', $users['test']->email);

        $this->assertInstanceOf(AuthUser::class, $users['test2']);
        $this->assertEquals(2, $users['test2']->id);
        $this->assertEquals('test2', $users['test2']->name);
        $this->assertEquals('test2', $users['test2']->username);
        $this->assertEquals('test2@hyvor.com', $users['test2']->email);

        Http::assertSent(function (Request $request) {
            $data = InternalApi::dataFromMessage($request->data()['message']);
            return $data['usernames'] === ['test', 'test2'];
        });
    }

    public function testFromUsername(): void
    {
        Http::fake([
            'https://hyvor.com/api/internal/auth/users/from/usernames' => Http::response([
                'test' => [
                    'id' => 1,
                    'name' => 'test',
                    'username' => 'test',
                    'email' => 'test@hyvor.com',
                ]
            ])
        ]);

        $user = $this->provider->fromUsername('test');

        $this->assertInstanceOf(AuthUser::class, $user);
        $this->assertEquals(1, $user->id);
        $this->assertEquals('test', $user->name);
        $this->assertEquals('test', $user->username);
        $this->assertEquals('test@hyvor.com', $user->email);

        Http::assertSent(function (Request $request) {
            $data = InternalApi::dataFromMessage($request->data()['message']);
            return $data['usernames'] === ['test'];
        });
    }

    public function testFromUsernameNotFound(): void
    {
        Http::fake([
            'https://hyvor.com/api/internal/auth/users/from/usernames' => Http::response([])
        ]);
        $user = $this->provider->fromUsername('test');
        $this->assertNull($user);
    }

    public function testFromEmails(): void
    {
        Http::fake([
            'https://hyvor.com/api/internal/auth/users/from/emails' => Http::response([
                'test@hyvor.com' => [
                    'id' => 1,
                    'name' => 'test',
                    'username' => 'test',
                    'email' => 'test@hyvor.com',
                ],
                'test2@hyvor.com' => [
                    'id' => 2,
                    'name' => 'test2',
                    'username' => 'test2',
                    'email' => 'test2@hyvor.com',
                ]
            ])
        ]);

        $users = $this->provider->fromEmails(['test@hyvor.com', 'test2@hyvor.com']);

        $this->assertInstanceOf(Collection::class, $users);
        $this->assertCount(2, $users);

        $this->assertInstanceOf(AuthUser::class, $users['test@hyvor.com']);
        $this->assertEquals(1, $users['test@hyvor.com']->id);
        $this->assertEquals('test', $users['test@hyvor.com']->name);
        $this->assertEquals('test', $users['test@hyvor.com']->username);
        $this->assertEquals('test@hyvor.com', $users['test@hyvor.com']->email);

        $this->assertInstanceOf(AuthUser::class, $users['test2@hyvor.com']);
        $this->assertEquals(2, $users['test2@hyvor.com']->id);
        $this->assertEquals('test2', $users['test2@hyvor.com']->name);
        $this->assertEquals('test2', $users['test2@hyvor.com']->username);
        $this->assertEquals('test2@hyvor.com', $users['test2@hyvor.com']->email);

        Http::assertSent(function (Request $request) {
            $data = InternalApi::dataFromMessage($request->data()['message']);
            return $data['emails'] === ['test@hyvor.com', 'test2@hyvor.com'];
        });
    }

    public function testFromEmail(): void
    {
        Http::fake([
            'https://hyvor.com/api/internal/auth/users/from/emails' => Http::response([
                'test@hyvor.com' => [
                    'id' => 1,
                    'name' => 'test',
                    'username' => 'test',
                    'email' => 'test@hyvor.com',
                ],
            ])
        ]);

        $user = $this->provider->fromEmail('test@hyvor.com');

        $this->assertInstanceOf(AuthUser::class, $user);
        $this->assertEquals(1, $user->id);
        $this->assertEquals('test', $user->name);
        $this->assertEquals('test', $user->username);
        $this->assertEquals('test@hyvor.com', $user->email);

        Http::assertSent(function (Request $request) {
            $data = InternalApi::dataFromMessage($request->data()['message']);
            return $data['emails'] === ['test@hyvor.com'];
        });
    }

    public function testFromEmailNotFound(): void
    {
        Http::fake([
            'https://hyvor.com/api/internal/auth/users/from/emails' => Http::response([])
        ]);
        $user = $this->provider->fromEmail('test@hyvor.com');
        $this->assertNull($user);
    }
}
