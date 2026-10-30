<?php

namespace Hyvor\Internal\Bundle\Comms\Event\FromCore\Resource;

use Hyvor\Internal\Bundle\Comms\Event\AbstractEvent;
use Hyvor\Internal\Component\Component;

class MigrateResource extends AbstractEvent
{

    public function __construct(
        private int $resourceId,
        private int $toOrganizationId,
    )
    {
    }

    public function getResourceId(): int
    {
        return $this->resourceId;
    }

    public function getToOrganizationId(): int
    {
        return $this->toOrganizationId;
    }

    public function from(): array
    {
        return [Component::CORE];
    }

    public function to(): array
    {
        return [];
    }
}
