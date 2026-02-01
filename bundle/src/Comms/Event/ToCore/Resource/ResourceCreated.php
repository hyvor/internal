<?php

namespace Hyvor\Internal\Bundle\Comms\Event\ToCore\Resource;

use Hyvor\Internal\Bundle\Comms\Event\AbstractEvent;
use Hyvor\Internal\Component\Component;

/**
 * Always send this event to core so that core can start a trial
 * for this organization if there is no trial
 */
class ResourceCreated extends AbstractEvent
{

    public function __construct(
        private Component $component,
        private int $organizationId,
        private ?\DateTimeImmutable $at = null,
    ) {}

    public function getComponent(): Component
    {
        return $this->component;
    }

    public function getOrganizationId(): int
    {
        return $this->organizationId;
    }

    public function getAt(): ?\DateTimeImmutable
    {
        return $this->at;
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
