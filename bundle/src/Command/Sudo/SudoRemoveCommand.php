<?php

namespace Hyvor\Internal\Bundle\Command\Sudo;

use Hyvor\Internal\Sudo\SudoUserService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\Argument;

#[AsCommand(
    name: 'sudo:remove',
    description: 'Remove a sudo user by their user ID',
)]
class SudoRemoveCommand extends Command
{

    public function __construct(
        private SudoUserService $sudoUserService
    ) {
        parent::__construct();
    }

    public function __invoke(
        #[Argument('ID of the user to remove.')] int $id,
        InputInterface $input,
        OutputInterface $output
    ): int {
        $user = $this->sudoUserService->get($id);

        if ($user === null) {
            $output->writeln("<error>No sudo user found with ID $id</error>");
            return Command::FAILURE;
        }

        $this->sudoUserService->remove($user);

        $output->writeln("<info>Sudo user with ID $id has been removed successfully.</info>");
        return Command::SUCCESS;
    }

}