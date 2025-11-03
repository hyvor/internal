<?php

namespace Hyvor\Internal\Tests\Unit\SelfHosted;

use Hyvor\Internal\SelfHosted\SelfHostedRecordTelemetryCommand;
use Hyvor\Internal\SelfHosted\SelfHostedTelemetryInterface;
use Hyvor\Internal\Tests\SymfonyTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SelfHostedRecordTelemetryCommand::class)]
class SelfHostedRecordTelemetryCommandTest extends SymfonyTestCase
{

    public function test_command(): void
    {
        $telemetryService = $this->createMock(SelfHostedTelemetryInterface::class);
        $telemetryService->expects($this->once())
            ->method('recordTelemetry');
        $this->container->set(SelfHostedTelemetryInterface::class, $telemetryService);

        $command = $this->getCommandTester('self-hosted:record-telemetry');
        $statusCode = $command->execute([]);

        $this->assertSame(0, $statusCode);
    }

}