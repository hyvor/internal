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
        // Meters
        $this->meter(
            name: 'base_credits',
            nameReadable: 'Additional Usage (Base Plan)',
            property: 'credits',
            pricePerUnit: 0.0004, // 4 per 10,000
        );

        // Version 1
        $this->version(1, function () {
            $this->plan(
                'base',
                30,
                new FortGuardLicense(
                    credits: 150_000
                ),
                nameReadable: 'Base Plan',
                monthlyOnly: true,
                meterName: 'base_credits',
            );
        });
    }
}
