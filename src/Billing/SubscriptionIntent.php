<?php

namespace Hyvor\Internal\Billing;

use Hyvor\Internal\Component\Component;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

/**
 * This is the object for requesting a new subscription
 * or a plan change.
 */
#[Exclude]
class SubscriptionIntent
{

    public function __construct(
        /**
         * Type of the component
         * Ex: `ComponentType::TALK` in talk
         * Ex: `ComponentType::BLOG` in blogs
         */
        public Component $component,

        /**
         * Version of the subscription plan as defined in each component's plan class.
         */
        public int $planVersion,

        /**
         * Name of the subscription plan.
         *
         * Ex: `premium_1` in talk
         * Ex: `premium` in blogs
         */
        public string $plan,

        /**
         * Organization requesting the subscription
         */
        public int $organizationId,

        /**
         * Monthly price of the subscription
         */
        public float $monthlyPrice,

        /**
         * Is this an annual subscription?
         */
        public bool $isAnnual,
    ) {
    }


}
