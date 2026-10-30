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
        private string $idempotencyKey,
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

    public function getIdempotencyKey(): string
    {
        return $this->idempotencyKey;
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
