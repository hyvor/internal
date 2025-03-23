<?php

namespace Hyvor\Internal\Tests\Unit\Auth;

use Hyvor\Internal\Auth\Auth;
use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Tests\TestCase;

class AuthFakeTest extends TestCase
{
    private function provider(): AuthFake
    {
        $provider = app(Auth::class);
        assert($provider instanceof AuthFake);
        return $provider;
    }

    public function testCheckBasedOnUserIdConfig(): void
    {
        AuthFake::enable(['id' => 1]);
        $this->assertEquals(1, $this->provider()->user?->id);

        AuthFake::enable(['id' => 2]);
        $this->assertEquals(2, $this->provider()->user?->id);

        AuthFake::enable(null);
        $this->assertNull($this->provider()->user);
    }

    public function testDatabaseHelperFunctions(): void
    {
        AuthFake::enable();
        AuthFake::databaseSet([
            ['id' => 1, 'name' => 'John'],
            ['id' => 2, 'name' => 'Jane'],
        ]);

        $db = AuthFake::databaseGet();
        $this->assertNotNull($db);
        $this->assertCount(2, $db);
        $this->assertEquals('John', $db[0]->name);
        $this->assertEquals('Jane', $db[1]->name);

        AuthFake::databaseAdd(['id' => 3, 'name' => 'Jack']);
        $this->assertCount(3, $db);
        $this->assertEquals('Jack', $db[2]->name);

        AuthFake::databaseClear();
        $this->assertNull(AuthFake::databaseGet());
    }

    public function test_database_add(): void
    {
        AuthFake::enable();
        AuthFake::databaseAdd(['id' => 3, 'name' => 'Jack']);
        $db = AuthFake::databaseGet();
        $this->assertNotNull($db);
        $this->assertCount(1, $db);
        $this->assertEquals('Jack', $db[0]->name);
    }

    public function test_database_set_user_array(): void
    {
        AuthFake::enable();
        AuthFake::databaseSet([
            AuthUser::fromArray([
                'id' => 1,
                'name' => 'Supun',
                'email' => 'supun@hyvor.com',
                'username' => 'supun'
            ])
        ]);
        $db = AuthFake::databaseGet();

        $this->assertNotNull($db);
        $this->assertEquals(1, $db[0]->id);
        $this->assertEquals('Supun', $db[0]->name);
    }

    public function testFromId(): void
    {
        AuthFake::enable();
        $id20 = $this->provider()->fromId(20);
        $this->assertNotNull($id20);
        $this->assertEquals(20, $id20->id);

        // with DB
        AuthFake::databaseSet([
            ['id' => 1, 'name' => 'John'],
            ['id' => 2, 'name' => 'Jane'],
        ]);

        $id1 = $this->provider()->fromId(1);
        $this->assertNotNull($id1);
        $this->assertEquals('John', $id1->name);
        $this->assertEquals(1, $id1->id);

        $id3 = $this->provider()->fromId(3);
        $this->assertNull($id3);
    }

    public function testFromEmail(): void
    {
        AuthFake::enable();
        $email20 = $this->provider()->fromEmail('20@test.com');
        $this->assertNotNull($email20);
        $this->assertEquals('20@test.com', $email20->email);

        // with DB
        AuthFake::databaseSet([
            ['id' => 1, 'name' => 'John', 'email' => 'john@test.com'],
            ['id' => 2, 'name' => 'Jane', 'email' => 'jane@test.com']
        ]);

        $email1 = $this->provider()->fromEmail('john@test.com');
        $this->assertNotNull($email1);
        $this->assertEquals('John', $email1->name);
        $this->assertEquals('john@test.com', $email1->email);

        $email3 = $this->provider()->fromEmail('supun@test.com');
        $this->assertNull($email3);
    }

    public function testFromUsername(): void
    {
        AuthFake::enable();
        $username20 = $this->provider()->fromUsername('user20');
        $this->assertNotNull($username20);
        $this->assertEquals('user20', $username20->username);

        // with DB
        AuthFake::databaseSet([
            ['id' => 1, 'name' => 'John', 'username' => 'john'],
            ['id' => 2, 'name' => 'Jane', 'username' => 'jane']
        ]);

        $username1 = $this->provider()->fromUsername('john');
        $this->assertNotNull($username1);
        $this->assertEquals('John', $username1->name);
        $this->assertEquals('john', $username1->username);

        $username3 = $this->provider()->fromUsername('supun');
        $this->assertNull($username3);
    }

    public function testFromIds(): void
    {
        AuthFake::enable();
        $ids = $this->provider()->fromIds([1, 2, 3]);
        $this->assertCount(3, $ids);
        $this->assertEquals(1, $ids[1]->id);
        $this->assertEquals(2, $ids[2]->id);
        $this->assertEquals(3, $ids[3]->id);

        // with DB
        AuthFake::databaseSet([
            ['id' => 1, 'name' => 'John', 'username' => 'john'],
            ['id' => 2, 'name' => 'Jane', 'username' => 'jane']
        ]);

        $ids = $this->provider()->fromIds([1, 2, 3]);
        $this->assertCount(2, $ids);
        $this->assertEquals(1, $ids[1]->id);
        $this->assertEquals(2, $ids[2]->id);
    }

    public function testFromUsernames(): void
    {
        AuthFake::enable();
        $usernames = $this->provider()->fromUsernames(['user1', 'user2', 'user3']);
        $this->assertCount(3, $usernames);
        $this->assertEquals('user1', $usernames['user1']->username);
        $this->assertEquals('user2', $usernames['user2']->username);
        $this->assertEquals('user3', $usernames['user3']->username);

        // with DB
        AuthFake::databaseSet([
            ['id' => 1, 'name' => 'John', 'username' => 'john'],
            ['id' => 2, 'name' => 'Jane', 'username' => 'jane']
        ]);

        $usernames = $this->provider()->fromUsernames(['john', 'jane', 'supun']);
        $this->assertCount(2, $usernames);
        $this->assertEquals('john', $usernames['john']->username);
        $this->assertEquals('jane', $usernames['jane']->username);
    }

    public function testFromEmails(): void
    {
        AuthFake::enable();
        $emails = $this->provider()->fromEmails(['user1@test.com', 'user2@test.com']);
        $this->assertCount(2, $emails);
        $this->assertEquals('user1@test.com', $emails['user1@test.com']->email);
        $this->assertEquals('user2@test.com', $emails['user2@test.com']->email);

        // with DB
        AuthFake::databaseSet([
            ['id' => 1, 'name' => 'John', 'email' => 'john@test.com'],
            ['id' => 2, 'name' => 'Jane', 'email' => 'jane@test.com']
        ]);

        $emails = $this->provider()->fromEmails(['john@test.com', 'jane@test.com', 'roger@test.com']);
        $this->assertCount(2, $emails);
        $this->assertEquals('john@test.com', $emails['john@test.com']->email);
        $this->assertEquals('jane@test.com', $emails['jane@test.com']->email);
    }
}
