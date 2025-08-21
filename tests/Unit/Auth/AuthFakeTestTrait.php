<?php

namespace Hyvor\Internal\Tests\Unit\Auth;

use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Auth\AuthUser;

trait AuthFakeTestTrait
{

    protected function getAuthFake(): AuthFake
    {
        $provider = $this->getContainer()->get(AuthInterface::class);
        assert($provider instanceof AuthFake);
        return $provider;
    }

    abstract protected function enable(?array $user = null): void;

    public function testCheckBasedOnUserIdConfig_1(): void
    {
        $this->enable(['id' => 1]);
        $this->assertSame(1, $this->getAuthFake()->user?->id);
    }

    public function testCheckBasedOnUserIdConfig_2(): void
    {
        $this->enable(['id' => 2]);
        $this->assertSame(2, $this->getAuthFake()->user?->id);
    }

    public function testCheckBasedOnUserIdConfig_3(): void
    {
        $this->enable(null);
        $this->assertNull($this->getAuthFake()->user);
    }


    public function test_auth_url(): void
    {
        $this->enable();

        $url = $this->getAuthFake()->authUrl('login', 'https://example.com/redirect');
        $this->assertSame(
            'https://hyvor.com/login?redirect=https%3A%2F%2Fexample.com%2Fredirect',
            $url
        );

        $url = $this->getAuthFake()->authUrl('signup');
        $this->assertSame('https://hyvor.com/signup', $url);
    }

    public function testDatabaseHelperFunctions(): void
    {
        $this->enable();
        AuthFake::databaseSet([
            ['id' => 1, 'name' => 'John'],
            ['id' => 2, 'name' => 'Jane'],
        ]);

        $db = AuthFake::databaseGet();
        $this->assertNotNull($db);
        $this->assertCount(2, $db);
        $this->assertSame('John', $db[0]->name);
        $this->assertSame('Jane', $db[1]->name);

        AuthFake::databaseAdd(['id' => 3, 'name' => 'Jack']);
        $db = AuthFake::databaseGet();
        $this->assertNotNull($db);
        $this->assertCount(3, $db);
        $this->assertSame('Jack', $db[2]->name);

        AuthFake::databaseClear();
        $this->assertNull(AuthFake::databaseGet());
    }

    public function test_database_add(): void
    {
        $this->enable();
        AuthFake::databaseAdd(['id' => 3, 'name' => 'Jack']);
        $db = AuthFake::databaseGet();
        $this->assertNotNull($db);
        $this->assertCount(1, $db);
        $this->assertSame('Jack', $db[0]->name);
    }

    public function test_database_set_user_array(): void
    {
        $this->enable();
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
        $this->assertSame(1, $db[0]->id);
        $this->assertSame('Supun', $db[0]->name);
    }

    public function testFromId(): void
    {
        $this->enable();
        $id20 = $this->getAuthFake()->fromId(20);
        $this->assertNotNull($id20);
        $this->assertSame(20, $id20->id);

        // with DB
        AuthFake::databaseSet([
            ['id' => 1, 'name' => 'John'],
            ['id' => 2, 'name' => 'Jane'],
        ]);

        $id1 = $this->getAuthFake()->fromId(1);
        $this->assertNotNull($id1);
        $this->assertSame('John', $id1->name);
        $this->assertSame(1, $id1->id);

        $id3 = $this->getAuthFake()->fromId(3);
        $this->assertNull($id3);
    }

    public function testFromEmail(): void
    {
        $this->enable();
        $email20 = $this->getAuthFake()->fromEmail('20@test.com');
        $this->assertCount(1, $email20);
        $email20 = $email20[0];
        $this->assertSame('20@test.com', $email20->email);

        // with DB - testing multiple users with same email
        AuthFake::databaseSet([
            ['id' => 1, 'name' => 'John', 'email' => 'john@test.com'],
            ['id' => 2, 'name' => 'Jane', 'email' => 'jane@test.com'],
            ['id' => 3, 'name' => 'Johnny', 'email' => 'john@test.com']
        ]);

        $email1 = $this->getAuthFake()->fromEmail('john@test.com');
        $this->assertCount(2, $email1);
        
        // Check first user (John)
        $this->assertSame('John', $email1[0]->name);
        $this->assertSame('john@test.com', $email1[0]->email);
        
        // Check second user (Johnny)
        $this->assertSame('Johnny', $email1[1]->name);
        $this->assertSame('john@test.com', $email1[1]->email);

        $email3 = $this->getAuthFake()->fromEmail('supun@test.com');
        $this->assertCount(0, $email3);
    }

    public function testFromUsername(): void
    {
        $this->enable();
        $username20 = $this->getAuthFake()->fromUsername('user20');
        $this->assertNotNull($username20);
        $this->assertSame('user20', $username20->username);

        // with DB
        AuthFake::databaseSet([
            ['id' => 1, 'name' => 'John', 'username' => 'john'],
            ['id' => 2, 'name' => 'Jane', 'username' => 'jane']
        ]);

        $username1 = $this->getAuthFake()->fromUsername('john');
        $this->assertNotNull($username1);
        $this->assertSame('John', $username1->name);
        $this->assertSame('john', $username1->username);

        $username3 = $this->getAuthFake()->fromUsername('supun');
        $this->assertNull($username3);
    }

    public function testFromIds(): void
    {
        $this->enable();
        $ids = $this->getAuthFake()->fromIds([1, 2, 3]);
        $this->assertCount(3, $ids);
        $this->assertSame(1, $ids[1]->id);
        $this->assertSame(2, $ids[2]->id);
        $this->assertSame(3, $ids[3]->id);

        // with DB
        AuthFake::databaseSet([
            ['id' => 1, 'name' => 'John', 'username' => 'john'],
            ['id' => 2, 'name' => 'Jane', 'username' => 'jane']
        ]);

        $ids = $this->getAuthFake()->fromIds([1, 2, 3]);
        $this->assertCount(2, $ids);
        $this->assertSame(1, $ids[1]->id);
        $this->assertSame(2, $ids[2]->id);
    }

    public function testFromUsernames(): void
    {
        $this->enable();
        $usernames = $this->getAuthFake()->fromUsernames(['user1', 'user2', 'user3']);
        $this->assertCount(3, $usernames);
        $this->assertSame('user1', $usernames['user1']->username);
        $this->assertSame('user2', $usernames['user2']->username);
        $this->assertSame('user3', $usernames['user3']->username);

        // with DB
        AuthFake::databaseSet([
            ['id' => 1, 'name' => 'John', 'username' => 'john'],
            ['id' => 2, 'name' => 'Jane', 'username' => 'jane']
        ]);

        $usernames = $this->getAuthFake()->fromUsernames(['john', 'jane', 'supun']);
        $this->assertCount(2, $usernames);
        $this->assertSame('john', $usernames['john']->username);
        $this->assertSame('jane', $usernames['jane']->username);
    }

    public function testFromEmails(): void
    {
        $this->enable();
        $emails = $this->getAuthFake()->fromEmails(['user1@test.com', 'user2@test.com']);
        $this->assertCount(2, $emails);

        $email1 = $emails['user1@test.com'];
        $this->assertCount(1, $email1);

        $email2 = $emails['user2@test.com'];
        $this->assertCount(1, $email2);

        $this->assertSame('user1@test.com', $email1[0]->email);
        $this->assertSame('user2@test.com', $email2[0]->email);

        // with DB
        AuthFake::databaseSet([
            ['id' => 1, 'name' => 'John', 'email' => 'john@test.com'],
            ['id' => 2, 'name' => 'Jane', 'email' => 'jane@test.com']
        ]);

        $emails = $this->getAuthFake()->fromEmails(['john@test.com', 'jane@test.com', 'roger@test.com']);
        $this->assertCount(2, $emails);
        $this->assertSame('john@test.com', $emails['john@test.com'][0]->email);
        $this->assertSame('jane@test.com', $emails['jane@test.com'][0]->email);
    }

}
