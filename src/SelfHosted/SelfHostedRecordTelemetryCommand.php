<?php

namespace Hyvor\Internal\SelfHosted;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

/**
 * Forcefully records telemetry.
 * Useful for local debugging.
 */
#[AsCommand('self-hosted:record-telemetry')]
class SelfHostedRecordTelemetryCommand
{

    public function __construct(private SelfHostedTelemetryInterface $selfHostedTelemetry)
    {
    }

    public function __invoke(): int
    {
        $this->selfHostedTelemetry->recordTelemetry();
        return Command::SUCCESS;
    }
}