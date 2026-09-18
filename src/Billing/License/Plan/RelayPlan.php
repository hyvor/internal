<?php

namespace Hyvor\Internal\Billing\License\Plan;

use Hyvor\Internal\Billing\License\RelayLicense;

/**
 * @extends PlanAbstract<RelayLicense>
 */
class RelayPlan extends PlanAbstract
{
    protected function config(): void
    {
        $this->version(1, function () {
            $this->plan(
                'starter',
                30,
                new RelayLicense(
                    emails:200_000,
                    projects: 3,
                    domains: 10,
                    dedicatedIps: false
                ),
                nameReadable: 'Starter',
                monthlyOnly: true
            );

            $this->plan(
                'pro',
                80,
                new RelayLicense(
                    emails: 800_000,
                    projects: 20,
                    domains: 1_000,
                    dedicatedIps: true
                ),
                nameReadable: 'Pro',
                monthlyOnly: true
            );
        });
    }
}