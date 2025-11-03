<?php

namespace Hyvor\Internal\SelfHosted;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

/**
 * Forcefully records telemetry.
 * Useful for local debugging.
 */
#[AsCommand('self-hosted:record-telemetry')]
class SelfHostedRecordTelemetryCommand
{

    public function __construct(
        private SelfHostedTelemetryInterface $selfHostedTelemetry,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(): int
    {
        $this->logger->debug('Recording self-hosted telemetry.');
        $this->selfHostedTelemetry->recordTelemetry();
        return Command::SUCCESS;
    }
}