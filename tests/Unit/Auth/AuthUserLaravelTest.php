<?php

namespace Hyvor\Internal\Tests\Unit\Auth;

use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Tests\LaravelTestCase;

class AuthUserLaravelTest extends LaravelTestCase
{

    private function getAuth(): AuthInterface
    {
        return app(AuthInterface::class);
    }

    public function testIsCreatedFromArray(): void
    {
        $attrs = [
            'id' => 1,
            'name' => 'John Doe',
            'username' => 'johndoe',
            'email' => 'john@hyvor.com',
            'picture_url' => 'https://hyvor.com/john.jpg',
        ];
        $user = AuthUser::fromArray($attrs);

        $this->assertEquals(1, $user->id);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('johndoe', $user->username);
        $this->assertEquals('john@hyvor.com', $user->email);
        $this->assertEquals('https://hyvor.com/john.jpg', $user->picture_url);
        $this->assertNull($user->location);
        $this->assertNull($user->bio);
        $this->assertNull($user->website_url);

        $this->assertSame([
            'id' => 1,
            'username' => 'johndoe',
            'name' => 'John Doe',
            'email' => 'john@hyvor.com',
            'picture_url' => 'https://hyvor.com/john.jpg',
            'location' => null,
            'bio' => null,
            'website_url' => null,
        ], $user->toArray());
    }

    public function testFromIds(): void
    {
        AuthFake::enable();
        $users = $this->getAuth()->fromIds([1, 2]);

        $this->assertCount(2, $users);
        $this->assertInstanceOf(AuthUser::class, $users[1]);
        $this->assertEquals(1, $users[1]->id);
        $this->assertEquals(2, $users[2]->id);

        $user = $this->getAuth()->fromId(3);

        $this->assertInstanceOf(AuthUser::class, $user);
        $this->assertEquals(3, $user->id);
    }


    public function testFromUsernames(): void
    {
        AuthFake::enable();
        $users = $this->getAuth()->fromUsernames(['johndoe', 'janedoe']);

        $this->assertCount(2, $users);
        $this->assertInstanceOf(AuthUser::class, $users['johndoe']);
        $this->assertEquals('johndoe', $users['johndoe']->username);
        $this->assertEquals('janedoe', $users['janedoe']->username);

        $user = $this->getAuth()->fromUsername('jimdoe');

        $this->assertInstanceOf(AuthUser::class, $user);
        $this->assertEquals('jimdoe', $user->username);
    }

    public function testFromEmails(): void
    {
        AuthFake::enable();
        $users = $this->getAuth()->fromEmails(['johndoe@hyvor.com', 'janedoe@hyvor.com']);

        $this->assertCount(2, $users);

        $this->assertEquals('johndoe@hyvor.com', $users['johndoe@hyvor.com'][0]->email);
        $this->assertEquals('janedoe@hyvor.com', $users['janedoe@hyvor.com'][0]->email);

        $user = $this->getAuth()->fromEmail('jimdoe@hyvor.com');

        $this->assertCount(1, $user);
        $this->assertEquals('jimdoe@hyvor.com', $user[0]->email);
    }

}
