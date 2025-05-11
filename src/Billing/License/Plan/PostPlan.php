<?php

namespace Hyvor\Internal\Billing\License\Plan;

use Hyvor\Internal\Billing\License\PostLicense;

/**
 * @extends PlanAbstract<PostLicense>
 */
class PostPlan extends PlanAbstract
{

    protected function config(): void
    {
        // Version 1
        $this->version(1, function () {
            $this->plan(
                '25k',
                10,
                new PostLicense(
                    emails: 25_000,
                    noBranding: false
                ),
                nameReadable: '25k Plan',
            );

            $this->plan(
                '100k',
                40,
                new PostLicense(
                    emails: 100_000,
                    noBranding: true
                ),
                nameReadable: '100k Plan',
            );

            $this->plan(
                '300k',
                100,
                new PostLicense(
                    emails: 300_000,
                    noBranding: true
                ),
                nameReadable: '300k Plan',
            );

            $this->plan(
                '1M',
                250,
                new PostLicense(
                    emails: 1_000_000,
                    noBranding: true
                ),
                nameReadable: '1M Plan',
            );
        });
    }

}