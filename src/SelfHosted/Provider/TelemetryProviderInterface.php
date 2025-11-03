<?php

namespace Hyvor\Internal\SelfHosted\Provider;

interface TelemetryProviderInterface
{

    /**
     * Calculate and save telemetry data in the class.
     * This method is called right before getInstanceUuid, getVersion and getPayload.
     * This method should be the only place to perform any data gathering logic.
     */
    public function initialize(): void;

    /**
     * UUID of the instance (one per deployment).
     */
    public function getInstanceUuid(): string;

    /**
     * Version of the image
     */
    public function getVersion(): string;

    /**
     * Any additional payload data (usually metrics)
     * @return array<string, mixed>
     */

    public function getPayload(): array;

}