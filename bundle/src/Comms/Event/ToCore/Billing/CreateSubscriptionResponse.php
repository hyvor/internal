<?php

namespace Hyvor\Internal\Bundle\Comms\Event\ToCore\Billing;

readonly class CreateSubscriptionResponse
{

    public function __construct(
        private int $subscriptionId,
    ) {
    }

    public function getSubscriptionId(): int
    {
        return $this->subscriptionId;
    }
}
