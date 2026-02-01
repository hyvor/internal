<?php

namespace Hyvor\Internal\Tests\Unit\Auth;

use Hyvor\Internal\Auth\Auth;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Auth\Dto\Me;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\User\GetMe;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\User\GetMeResponse;
use Hyvor\Internal\Bundle\Comms\MockComms;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\InternalApi\InternalApi;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\HttpFoundation\Request;

trait AuthTestTrait
{

    private function getAuth(): Auth
    {
        /** @var Auth $auth */
        $auth = $this->getContainer()->get(Auth::class);
        return $auth;
    }

    private function getInternalApi(): InternalApi
    {
        /** @var InternalApi $internalApi */
        $internalApi = $this->getContainer()->get(InternalApi::class);
        return $internalApi;
    }

    private function setResponseFactory(JsonMockResponse $response): void
    {
        $this->setHttpClientResponse($response);
    }

    private function requestWithCookie(string $cookie): Request
    {
        $request = Request::create('https://hyvor.internal/api/internal/auth/check');
        $request->cookies->set(Auth::HYVOR_SESSION_COOKIE_NAME, $cookie);
        return $request;
    }

    public function testCheckWhenNoCookieSet(): void
    {
        $this->assertNull($this->getAuth()->me($this->requestWithCookie('')));
    }

    abstract function setComms(MockComms $comms): void;

    public function testCheckWhenCookieIsSet(): void
    {
        /** @var MockComms $mockComms */
        $mockComms = $this->getContainer()->get(MockComms::class);
        $mockComms->addResponse(GetMe::class, new GetMeResponse(
            user: AuthUser::fromArray([
                'id' => 1,
                'name' => 'test',
                'username' => 'test',
                'email' => 'test@test.com'
            ])
        ));
        $this->setComms($mockComms);

        $me = $this->getAuth()->me($this->requestWithCookie('test-cookie'));

        $this->assertInstanceOf(Me::class, $me);

        $user = $me->getUser();
        $this->assertInstanceOf(AuthUser::class, $user);
        $this->assertEquals(1, $user->id);
        $this->assertEquals('test', $user->name);
        $this->assertEquals('test', $user->username);
        $this->assertEquals('test@test.com', $user->email);

        $mockComms->assertSent(GetMe::class, Component::CORE,
            eventValidator: fn ($me) => $this->assertSame('test-cookie', $me->getCookie()));
    }

    public function testReturnsFalseWhenUserIsNull(): void
    {

        /** @var MockComms $mockComms */
        $mockComms = $this->getContainer()->get(MockComms::class);
        $mockComms->addResponse(GetMe::class, new GetMeResponse(
            user: null
        ));
        $this->setComms($mockComms);
        $this->assertNull($this->getAuth()->me($this->requestWithCookie('test')));
    }

    public function test_auth_url(): void
    {
        $auth = $this->getAuth();

        $this->assertSame(
            'https://hyvor.com/login',
            $auth->authUrl('login')
        );

        $this->assertSame(
            'https://hyvor.com/login?redirect=https%3A%2F%2Fexample.com',
            $auth->authUrl('login', 'https://example.com')
        );

        $request = Request::create('https://example.com/path/to/page');

        $this->assertSame(
            'https://hyvor.com/signup?redirect=https%3A%2F%2Fexample.com%2Fpath%2Fto%2Fpage',
            $auth->authUrl('signup', $request)
        );
    }

    public function test_from_ids_empty(): void
    {
        $empty = $this->getAuth()->fromIds([]);
        $this->assertCount(0, $empty);
    }


    public function testFromIds(): void
    {
        $response = new JsonMockResponse([
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
        ]);
        $this->setResponseFactory($response);

        $users = $this->getAuth()->fromIds([1, 2]);
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

        $this->assertSame(
            'https://hyvor.internal/api/internal/auth/users/from/ids',
            $response->getRequestUrl()
        );

        $data = $this->getInternalApi()->dataFromMockResponse($response);
        $this->assertEquals([1, 2], $data['ids']);
    }

    public function testFromId(): void
    {
        $response = new JsonMockResponse([
            1 => [
                'id' => 1,
                'name' => 'test',
                'username' => 'test',
                'email' => 'test@hyvor.com',
                'picture_url' => 'https://hyvor.com/avatar.png'
            ]
        ]);
        $this->setResponseFactory($response);

        $user = $this->getAuth()->fromId(1);

        $this->assertInstanceOf(AuthUser::class, $user);
        $this->assertEquals(1, $user->id);
        $this->assertEquals('test', $user->name);
        $this->assertEquals('test', $user->username);
        $this->assertEquals('test@hyvor.com', $user->email);
        $this->assertEquals('https://hyvor.com/avatar.png', $user->picture_url);

        $this->assertSame(
            'https://hyvor.internal/api/internal/auth/users/from/ids',
            $response->getRequestUrl()
        );
        $data = $this->getInternalApi()->dataFromMockResponse($response);
        $this->assertEquals([1], $data['ids']);
    }

    public function testFromIdNotFound(): void
    {
        $response = new JsonMockResponse([]);
        $this->setResponseFactory($response);
        $user = $this->getAuth()->fromId(1);
        $this->assertNull($user);
    }

    public function testFromUsernames(): void
    {
        $response = new JsonMockResponse([
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
        ]);
        $this->setResponseFactory($response);

        $users = $this->getAuth()->fromUsernames(['test', 'test2']);
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

        $this->assertSame(
            'https://hyvor.internal/api/internal/auth/users/from/usernames',
            $response->getRequestUrl()
        );
        $data = $this->getInternalApi()->dataFromMockResponse($response);
        $this->assertEquals(['test', 'test2'], $data['usernames']);
    }

    public function testFromUsername(): void
    {
        $response = new JsonMockResponse([
            'test' => [
                'id' => 1,
                'name' => 'test',
                'username' => 'test',
                'email' => 'test@hyvor.com',
            ]
        ]);
        $this->setResponseFactory($response);

        $user = $this->getAuth()->fromUsername('test');

        $this->assertInstanceOf(AuthUser::class, $user);
        $this->assertEquals(1, $user->id);
        $this->assertEquals('test', $user->name);
        $this->assertEquals('test', $user->username);
        $this->assertEquals('test@hyvor.com', $user->email);

        $this->assertSame(
            'https://hyvor.internal/api/internal/auth/users/from/usernames',
            $response->getRequestUrl()
        );
        $data = $this->getInternalApi()->dataFromMockResponse($response);
        $this->assertEquals(['test'], $data['usernames']);
    }

    public function testFromUsernameNotFound(): void
    {
        $response = new JsonMockResponse([]);
        $this->setResponseFactory($response);
        $user = $this->getAuth()->fromUsername('test');
        $this->assertNull($user);
    }

    public function testFromEmails(): void
    {
        $response = new JsonMockResponse([
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
        ]);
        $this->setResponseFactory($response);

        $users = $this->getAuth()->fromEmails(['test@hyvor.com', 'test2@hyvor.com']);
        $this->assertCount(2, $users);

        $user1 = $users['test@hyvor.com'];
        $this->assertCount(1, $user1);
        $user1 = $user1[0];

        $this->assertInstanceOf(AuthUser::class, $user1);
        $this->assertEquals(1, $user1->id);
        $this->assertEquals('test', $user1->name);
        $this->assertEquals('test', $user1->username);
        $this->assertEquals('test@hyvor.com', $user1->email);

        $user2 = $users['test2@hyvor.com'];
        $this->assertCount(1, $user2);
        $user2 = $user2[0];

        $this->assertInstanceOf(AuthUser::class, $user2);
        $this->assertEquals(2, $user2->id);
        $this->assertEquals('test2', $user2->name);
        $this->assertEquals('test2', $user2->username);
        $this->assertEquals('test2@hyvor.com', $user2->email);

        $this->assertSame(
            'https://hyvor.internal/api/internal/auth/users/from/emails',
            $response->getRequestUrl()
        );
        $data = $this->getInternalApi()->dataFromMockResponse($response);
        $this->assertSame(['test@hyvor.com', 'test2@hyvor.com'], $data['emails']);
    }

    public function testFromEmail(): void
    {
        $response = new JsonMockResponse([
            'test@hyvor.com' => [
                'id' => 1,
                'name' => 'test',
                'username' => 'test',
                'email' => 'test@hyvor.com',
            ],
        ]);
        $this->setResponseFactory($response);

        $user = $this->getAuth()->fromEmail('test@hyvor.com');
        $this->assertCount(1, $user);
        $user = $user[0];

        $this->assertInstanceOf(AuthUser::class, $user);
        $this->assertEquals(1, $user->id);
        $this->assertEquals('test', $user->name);
        $this->assertEquals('test', $user->username);
        $this->assertEquals('test@hyvor.com', $user->email);

        $this->assertSame(
            'https://hyvor.internal/api/internal/auth/users/from/emails',
            $response->getRequestUrl()
        );
        $data = $this->getInternalApi()->dataFromMockResponse($response);
        $this->assertSame(['test@hyvor.com'], $data['emails']);
    }

    public function testFromEmailNotFound(): void
    {
        $response = new JsonMockResponse([]);
        $this->setResponseFactory($response);
        $user = $this->getAuth()->fromEmail('test@hyvor.com');
        $this->assertCount(0, $user);
    }

}
