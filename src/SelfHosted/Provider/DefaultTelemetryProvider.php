<?php

namespace Hyvor\Internal\SelfHosted\Provider;

/**
 * @codeCoverageIgnore
 */
class DefaultTelemetryProvider implements TelemetryProviderInterface
{

    private function error(): void
    {
        throw new \RuntimeException(
            'No telemetry provider configured. Please implement TelemetryProviderInterface and bind it to the container.'
        );
    }

    public function initialize(): void
    {
        $this->error();
    }

    public function getInstanceUuid(): string
    {
        $this->error();
        return '';
    }

    public function getVersion(): string
    {
        $this->error();
        return '';
    }

    public function getPayload(): array
    {
        $this->error();
        return [];
    }
}