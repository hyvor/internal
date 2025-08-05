<?php

namespace Hyvor\Internal\Tests\Bundle\Command\Sudo;

use Hyvor\Internal\Bundle\Entity\SudoUser;
use Hyvor\Internal\Tests\SymfonyTestCase;
use Hyvor\Internal\Tests\Unit\Sudo\SudoUserFactoryTrait;
use Symfony\Component\Console\Command\Command;

class SudoRemoveCommandTest extends SymfonyTestCase
{

    use SudoUserFactoryTrait;

    public function test_fails_when_no_sudo_user_found(): void
    {
        $command = $this->getCommandTester('sudo:remove');
        $command->execute(['id' => 999]);

        $output = $command->getDisplay();
        $this->assertStringContainsString('No sudo user found with ID 999', $output);
        $this->assertSame(Command::FAILURE, $command->getStatusCode());
    }

    public function test_removes_sudo_user_successfully(): void
    {
        $sudoUser = $this->createSudoUser(userId: 42, em: $this->em);

        $command = $this->getCommandTester('sudo:remove');
        $command->execute(['id' => 42]);

        $output = $command->getDisplay();
        $this->assertStringContainsString(
            "Sudo user with ID 42 has been removed successfully.",
            $output
        );
        $this->assertSame(Command::SUCCESS, $command->getStatusCode());

        $sudoUsers = $this->em->getRepository(SudoUser::class)->findAll();
        $this->assertCount(0, $sudoUsers);
    }

}