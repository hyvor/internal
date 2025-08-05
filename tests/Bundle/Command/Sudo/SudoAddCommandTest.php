<?php

namespace Hyvor\Internal\Tests\Bundle\Command\Sudo;

use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Auth\Oidc\OidcAuth;
use Hyvor\Internal\Bundle\Command\Sudo\SudoAddCommand;
use Hyvor\Internal\Bundle\Entity\SudoUser;
use Hyvor\Internal\Sudo\SudoUserService;
use Hyvor\Internal\Tests\SymfonyTestCase;
use Hyvor\Internal\Tests\Unit\Auth\Oidc\OidcUserFactoryTrait;
use Hyvor\Internal\Tests\Unit\Sudo\SudoUserFactoryTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Command\Command;

#[CoversClass(SudoAddCommand::class)]
#[CoversClass(SudoUserService::class)]
#[CoversClass(SudoUser::class)]
class SudoAddCommandTest extends SymfonyTestCase
{

    use OidcUserFactoryTrait;
    use SudoUserFactoryTrait;

    public function test_fails_on_invalid_email(): void
    {
        $command = $this->getCommandTester('sudo:add');
        $command->execute([
            'email' => 'invalid-email',
        ]);

        $output = $command->getDisplay();
        $this->assertStringContainsString('Invalid email format', $output);
        $this->assertSame(Command::FAILURE, $command->getStatusCode());
    }

    public function test_fails_when_no_user_found_for_email(): void
    {
        AuthFake::enableForSymfony($this->container, usersDatabase: []);

        $command = $this->getCommandTester('sudo:add');
        $command->execute([
            'email' => 'supun@hyvor.com'
        ]);

        $output = $command->getDisplay();
        $this->assertStringContainsString('No user found with the provided email', $output);
        $this->assertSame(Command::FAILURE, $command->getStatusCode());
    }

    public function test_fails_when_user_is_already_sudo(): void
    {
        AuthFake::enableForSymfony($this->container, usersDatabase: [
            ['id' => 11, 'email' => 'supun@hyvor.com', 'name' => 'Supun'],
        ]);

        $this->createSudoUser(userId: 11, em: $this->em);

        $command = $this->getCommandTester('sudo:add');
        $command->execute([
            'email' => 'supun@hyvor.com',
        ]);

        $output = $command->getDisplay();
        $this->assertStringContainsString('This user is already a sudo user.', $output);
        $this->assertSame(Command::FAILURE, $command->getStatusCode());
    }

    public function test_adds_user_when_one_user_found(): void
    {
        AuthFake::enableForSymfony($this->container, usersDatabase: [
            ['id' => 1, 'email' => 'supun@hyvor.com', 'name' => 'Supun'],
        ]);

        $command = $this->getCommandTester('sudo:add');
        $command->execute([
            'email' => 'supun@hyvor.com',
        ]);

        $output = $command->getDisplay();
        $this->assertStringContainsString('Added user as sudo: supun@hyvor.com (ID: 1)', $output);
        $this->assertSame(Command::SUCCESS, $command->getStatusCode());

        $sudoUsers = $this->em->getRepository(SudoUser::class)->findAll();
        $this->assertCount(1, $sudoUsers);
        $this->assertSame(1, $sudoUsers[0]->getUserId());
        $this->assertInstanceOf(\DateTimeImmutable::class, $sudoUsers[0]->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $sudoUsers[0]->getUpdatedAt());
    }

    public function test_asks_question_when_multiple_users_found(): void
    {
        /** @var OidcAuth $oidcAuth */
        $oidcAuth = $this->container->get(OidcAuth::class);
        $this->container->set(AuthInterface::class, $oidcAuth);

        $oidcUser1 = $this->createOidcUser(name: 'Test User 1', sub: 'sub1', em: $this->em);
        $oidcUser2 = $this->createOidcUser(name: 'Test User 2', sub: 'sub2', em: $this->em);

        $command = $this->getCommandTester('sudo:add');
        $command->setInputs(['1']);
        $command->execute([
            'email' => 'test@hyvor.com',
        ]);

        $output = $command->getDisplay();
        $this->assertStringContainsString('Added user as sudo: test@hyvor.com (ID: 2) (OIDC sub: sub2)', $output);
        $this->assertSame(Command::SUCCESS, $command->getStatusCode());

        $sudoUsers = $this->em->getRepository(SudoUser::class)->findAll();
        $this->assertCount(1, $sudoUsers);
        $this->assertSame($oidcUser2->getId(), $sudoUsers[0]->getUserId());
    }

}