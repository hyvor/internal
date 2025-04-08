<?php

namespace Hyvor\Internal\Billing\License\Plan;

use Hyvor\Internal\Billing\License\TalkLicense;

/**
 * @extends PlanAbstract<TalkLicense>
 */
class TalkPlan extends PlanAbstract
{

    protected function config(): void
    {
        $this->version(1, function () {
            $this->premiumPlan(100_000, 12);
            $this->premiumPlan(500_000, 35);
            $this->premiumPlan(1_000_000, 65);
            $this->premiumPlan(2_000_000, 105);
            $this->premiumPlan(5_000_000, 245);

            $this->businessPlan(250_000, 40);
            $this->businessPlan(500_000, 75);
            $this->businessPlan(1_000_000, 135);
            $this->businessPlan(2_000_000, 245);
            $this->businessPlan(5_000_000, 515);
        });
    }


    private function premiumPlan(int $credits, float $monthlyPrice): void
    {
        $license = new TalkLicense(
            credits: $credits,
            storage: $this->getStorageBytesFromCredits($credits),
            sso: false,
            noBranding: false,
            webhooks: false,
        );

        $nameSuffix = $credits / 1_000_000;
        $nameReadableSuffix = $credits >= 1_000_000 ?
            $credits / 1_000_000 . 'M' :
            $credits / 1_000 . 'k';

        $this->plan(
            'premium_' . $nameSuffix,
            $monthlyPrice,
            $license,
            nameReadable: "Premium ($nameReadableSuffix)",
            group: 'premium'
        );
    }

    private function businessPlan(int $credits, float $monthlyPrice): void
    {
        $license = new TalkLicense(
            credits: $credits,
            storage: $this->getStorageBytesFromCredits($credits),
            sso: true,
            noBranding: true,
            webhooks: true,
        );

        $nameSuffix = $credits / 1_000_000;
        $nameReadableSuffix = $credits >= 1_000_000 ?
            $credits / 1_000_000 . 'M' :
            $credits / 1_000 . 'k';

        $this->plan(
            'business_' . $nameSuffix,
            $monthlyPrice,
            $license,
            nameReadable: "Business ($nameReadableSuffix)",
            group: 'business'
        );
    }

    /**
     * Users get 100GB for 1M credits.
     */
    private function getStorageBytesFromCredits(int $credits): int
    {
        $creditsMil = $credits / 1_000_000;
        $gbBytes = 10 ** 9;
        return $creditsMil * 100 * $gbBytes;
    }

}
