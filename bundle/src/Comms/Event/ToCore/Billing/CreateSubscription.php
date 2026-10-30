<?php

namespace Hyvor\Internal\Bundle\Comms\Event\ToCore\Billing;

use Hyvor\Internal\Bundle\Comms\Event\AbstractEvent;
use Hyvor\Internal\Component\Component;

/**
 * @extends AbstractEvent<CreateSubscriptionResponse>
 */
class CreateSubscription extends AbstractEvent
{

    public function __construct(
        private int $organizationId,
        private Component $component,
        private string $plan,
    ) {
    }

    public function getOrganizationId(): int
    {
        return $this->organizationId;
    }

    public function getComponent(): Component
    {
        return $this->component;
    }

    public function getPlan(): string
    {
        return $this->plan;
    }

    public function from(): array
    {
        return [];
    }

    public function to(): array
    {
        return [Component::CORE];
    }

}
