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
        $this->meter(
            name: 'starter_emails',
            nameReadable: 'Additional Emails (Starter)',
            property: 'emails',
            pricePerUnit: 0.0002, // €2 per 10,000
        );

        $this->meter(
            name: 'pro_emails',
            nameReadable: 'Additional Emails (Pro)',
            property: 'emails',
            pricePerUnit: 0.0001, // €1 per 10,000
        );

        $this->version(1, function () {
            $this->plan(
                'starter',
                30,
                new RelayLicense(
                    emails: 150_000,
                    projects: 3,
                    domains: 10,
                    dedicatedIps: false
                ),
                nameReadable: 'Starter',
                monthlyOnly: true,
                meterName: 'starter_emails',
            );

            $this->plan(
                'pro',
                90,
                new RelayLicense(
                    emails: 900_000,
                    projects: 5,
                    domains: 100,
                    dedicatedIps: true
                ),
                nameReadable: 'Pro',
                monthlyOnly: true,
                meterName: 'pro_emails',
            );
        });
    }
}
