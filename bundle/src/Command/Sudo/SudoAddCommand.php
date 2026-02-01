<?php

namespace Hyvor\Internal\Bundle\Command\Sudo;

use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Sudo\SudoUserService;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

#[AsCommand(
    name: 'sudo:add',
    description: 'Add a sudo user by email.'
)]
class SudoAddCommand extends Command
{

    public function __construct(
        private AuthInterface $auth,
        private SudoUserService $sudoUserService,
    ) {
        parent::__construct();
    }

    public function __invoke(
        #[Argument('Email of the user to add as sudo')] string $email,
        InputInterface $input,
        OutputInterface $output
    ): int {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $output->writeln('<error>Invalid email format.</error>');
            return Command::FAILURE;
        }

        $usersWithThatEmail = $this->auth->fromEmail($email);

        if (count($usersWithThatEmail) === 0) {
            $output->writeln('<error>No user found with the provided email.</error>');
            return Command::FAILURE;
        }

        if (count($usersWithThatEmail) === 1) {
            $user = $usersWithThatEmail[0];
        } else {
            /** @var array<\Stringable> $answers */
            $answers = array_map(fn($u) => $this->userRow($u), $usersWithThatEmail);
            $helper = new QuestionHelper();
            $question = new ChoiceQuestion(
                'Multiple users found with the provided email. Please select one:',
                $answers,
                0
            );

            /** @var object{user: AuthUser} $value */
            $value = $helper->ask($input, $output, $question);

            $user = $value->user;
        }

        if ($this->sudoUserService->exists($user->id)) {
            $output->writeln('<error>This user is already a sudo user.</error>');
            return Command::FAILURE;
        }

        $this->sudoUserService->create($user->id);

        $oidcSub = $user->oidc_sub ? ' (OIDC sub: ' . $user->oidc_sub . ')' : '';
        $output->writeln(
            sprintf(
                '<info>Added user as sudo: %s (ID: %d)%s</info>',
                $user->email,
                $user->id,
                $oidcSub
            )
        );

        return Command::SUCCESS;
    }


    private function userRow(AuthUser $user): object
    {
        return new readonly class($user) implements \Stringable {

            public function __construct(public AuthUser $user)
            {
            }

            public function __toString(): string
            {
                $row = sprintf(
                    '%s (%s) (ID: %d)',
                    $this->user->name,
                    $this->user->email,
                    $this->user->id,
                );
                if ($this->user->oidc_sub) {
                    $row .= ' (OIDC sub: ' . $this->user->oidc_sub . ')';
                }
                return $row;
            }

        };
    }

}
