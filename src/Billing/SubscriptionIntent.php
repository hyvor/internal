<?php

namespace Hyvor\Internal\Billing;

use Hyvor\Internal\Component\Component;
use Hyvor\Internal\Util\Transfer\Encryptable;

/**
 * This is the object for requesting a new subscription
 * or a plan change.
 */
class SubscriptionIntent
{

    use Encryptable;

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
         * User requesting the subscription
         */
        public int $userId,

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
