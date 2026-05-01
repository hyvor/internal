<?php

namespace Hyvor\Internal\Billing\License\Plan;

use Hyvor\Internal\Billing\License\FortguardLicense;

// 20k 
// 50k - 40k

// 1st - 50k

// 50k 50€
// 20k - 40k

/**
 * @extends PlanAbstract<FortguardLicense>
 */
class FortguardPlan extends PlanAbstract
{

    protected function config(): void
    {
        // Meters
        $this->meter(
            name: 'base_credits',
            nameReadable: 'Extra Credits',
            property: 'credits',
            pricePerUnit: 0.0004, // 4 per 10,000
        );

        // Version 1
        $this->version(1, function () {
            $this->plan(
                'base',
                30,
                new FortguardLicense(credits: 150_000),
                nameReadable: 'Base',
                meterName: 'base_credits',
            );
        });
    }
}
