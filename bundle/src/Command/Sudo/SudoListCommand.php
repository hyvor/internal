<?php

namespace Hyvor\Internal\Bundle\Command\Sudo;

use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Auth\Oidc\OidcAuth;
use Hyvor\Internal\Billing\BillingInterface;
use Hyvor\Internal\Sudo\SudoUserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'sudo:list',
    description: 'List sudo users.'
)]
class SudoListCommand extends Command
{

    private bool $isOidc;

    public function __construct(
        private AuthInterface $auth,
        private SudoUserRepository $sudoUserRepository
    ) {
        $this->isOidc = $auth instanceof OidcAuth;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = new Table($output);
        $headers = ['User ID', 'Email', 'Name'];

        if ($this->isOidc) {
            $headers[] = 'OIDC sub';
        }

        $table->setHeaders($headers);

        $rows = $this->getSudoUserRows();
        $table->addRows($rows);

        $table->render();

        return Command::SUCCESS;
    }

    /**
     * @return array<mixed>
     */
    private function getSudoUserRows(): array
    {
        $sudoUsers = $this->sudoUserRepository->findAll();
        $userIds = array_map(fn($user) => $user->getUserId(), $sudoUsers);
        $authUsers = $this->auth->fromIds($userIds);

        $result = [];

        foreach ($sudoUsers as $sudoUser) {
            $userId = $sudoUser->getUserId();
            $authUser = $authUsers[$userId] ?? null;

            if ($authUser) {
                $row = [
                    $userId,
                    $authUser->email,
                    $authUser->name,
                ];

                if ($this->isOidc) {
                    $row[] = $authUser->oidc_sub ?? 'N/A';
                }

                $result[] = $row;
            }
        }

        return $result;
    }


}