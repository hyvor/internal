<?php

namespace Hyvor\Internal\Bundle\Comms\Event\ToCore\Billing;

use Hyvor\Internal\Bundle\Comms\Event\AbstractEvent;
use Hyvor\Internal\Component\Component;

class RecordMeteredUsage extends AbstractEvent
{
    public function __construct(
        private Component $component,
        private int $organizationId,
        private int $amount,
        private \DateTimeImmutable $usageDate,
        private bool $force = false,
    ) {}

    public function getComponent(): Component
    {
        return $this->component;
    }

    public function getOrganizationId(): int
    {
        return $this->organizationId;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getUsageDate(): \DateTimeImmutable
    {
        return $this->usageDate;
    }

    public function isForce(): bool
    {
        return $this->force;
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
