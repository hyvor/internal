<?php

namespace Hyvor\Internal\Tests\Unit\Auth;

use Hyvor\Internal\Auth\Auth;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\InternalApi\InternalApi;
use Illuminate\Support\Collection;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

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
        $httpClient = $this->getContainer()->get(HttpClientInterface::class);
        assert($httpClient instanceof MockHttpClient);
        $httpClient->setResponseFactory($response);
    }

    private function requestWithCookie(string $cookie): Request
    {
        $request = Request::create('https://hyvor.internal/api/internal/auth/check');
        $request->cookies->set('hyvor_auth', $cookie);
        return $request;
    }

    public function testCheckWhenNoCookieSet(): void
    {
        $this->assertFalse($this->getAuth()->check($this->requestWithCookie('')));
    }

    public function testCheckWhenCookieIsSet(): void
    {
        $response = new JsonMockResponse([
            'user' => [
                'id' => 1,
                'name' => 'test',
                'username' => 'test',
                'email' => 'test@test.com'
            ]
        ]);
        $this->setResponseFactory($response);

        $user = $this->getAuth()->check($this->requestWithCookie('test-cookie'));

        $this->assertInstanceOf(AuthUser::class, $user);
        $this->assertEquals(1, $user->id);
        $this->assertEquals('test', $user->name);
        $this->assertEquals('test', $user->username);
        $this->assertEquals('test@test.com', $user->email);

        $this->assertSame(
            'https://hyvor.internal/api/internal/auth/check',
            $response->getRequestUrl()
        );

        $data = $this->getInternalApi()->dataFromMockResponse($response);
        $this->assertEquals('test-cookie', $data['cookie']);
    }

    public function testReturnsFalseWhenUserIsNull(): void
    {
        $response = new JsonMockResponse([
            'user' => null
        ]);
        $this->setResponseFactory($response);
        $this->assertFalse($this->getAuth()->check($this->requestWithCookie('test')));
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
        $this->assertNull($user);
    }

}