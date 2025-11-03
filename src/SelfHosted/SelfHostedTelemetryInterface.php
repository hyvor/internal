<?php

namespace Hyvor\Internal\SelfHosted;

use Hyvor\Internal\Component\Component;

interface SelfHostedTelemetryInterface
{
    public function recordTelemetry();
}