<?php

namespace Hyvor\Internal\Billing;

use Symfony\Component\DependencyInjection\Attribute\Exclude;

/**
 * Temporary class for generating migrations from a product to core.
 * If the user has multiple subscriptions for resources, send only the largest one.
 * @codeCoverageIgnore
 */
#[Exclude]
class MigratingSubscription
{

    public function __construct(

        public int $userId,

        /**
         * The price will be selected from this plan.
         */
        public int $planVersion,
        public string $plan,

        // whether the subscription is annual
        public bool $isAnnual,

        // subscription is canceled, but effective on this date
        public ?int $cancelAt,

        /**
         * If it is a paddle subscription, set it to the Paddle subscription ID.
         * The migration script will automatically fetch the subscription details from Paddle. (such as billing dates)
         */
        public ?string $paddleSubscriptionId,
    ) {
    }

}
