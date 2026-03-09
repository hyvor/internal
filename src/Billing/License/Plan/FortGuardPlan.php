<?php

namespace Hyvor\Internal\Billing\License\Plan;

use Hyvor\Internal\Billing\License\FortGuardLicense;

/**
 * @extends PlanAbstract<FortGuardLicense>
 */
class FortGuardPlan extends PlanAbstract
{
    protected function config(): void
    {
        // Version 1
        $this->version(1, function () {
            $this->plan(
                'base',
                30,
                new FortGuardLicense(
                    credits: 150_000
                ),
                nameReadable: 'Base Plan',
            );
        });
    }
}
