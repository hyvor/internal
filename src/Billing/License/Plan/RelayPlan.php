<?php

namespace Hyvor\Internal\Billing\License\Plan;

class RelayPlan extends PlanAbstract
{
    /**
     * @codeCoverageIgnore
     */
    protected function config(): void
    {
        $this->version(1, function () {
        });
    }
}