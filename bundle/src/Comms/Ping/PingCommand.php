<?php

namespace Hyvor\Internal\Bundle\Comms\Ping;

use Hyvor\Internal\Bundle\Comms\CommsInterface;
use Hyvor\Internal\Bundle\Comms\Exception\CommsApiFailedException;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\Component\InstanceUrlResolver;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @codeCoverageIgnore
 */
#[AsCommand(
    name: 'comms:ping',
    description: 'Ping other components in the instance for comms connectivity')
]
class PingCommand
{

    public function __construct(
        private InstanceUrlResolver $instanceUrlResolver,
        private CommsInterface $comms
    ) {}

    public function __invoke(
        InputInterface $input,
        OutputInterface $output,

        #[Argument('Component to ping. Pings all if not set')]
        string $component = '',
    ): int
    {
        assert($output instanceof ConsoleOutputInterface);

        if ($component === '') {
            $components = Component::cases();
        } else {
            $components = [Component::from($component)];
        }

        $loadingSection = $output->section();
        $loadingSection->setMaxHeight(1);

        $tableSection = $output->section();
        $table = new Table($tableSection);
        $table->setHeaders(['Component', 'URL', 'Status', 'Latency']);
        $table->setStyle('box');
        $table->render();

        foreach ($components as $c) {

            $loadingSection->writeln('Pinging ' . $c->value . '...');

            $success = false;
            $start = microtime(true);

            try {
                $this->comms->send(new PingEvent(), $c);
                $success = true;
            } catch (CommsApiFailedException $e) {}

            $end = microtime(true);
            $latency = round(($end - $start) * 1000);

            $table->appendRow(
                [
                    $c->value,
                    $this->instanceUrlResolver->privateUrlOf($c),
                    $success ? '✅' : '❌',
                    $latency . 'ms'
                ]
            );
        }

        $loadingSection->clear();
        $output->writeln('');

        return Command::SUCCESS;
    }

}
