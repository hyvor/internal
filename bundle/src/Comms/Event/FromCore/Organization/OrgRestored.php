<?php

namespace Hyvor\Internal\Bundle\Comms\Event\FromCore\Organization;

use Hyvor\Internal\Bundle\Comms\Event\AbstractEvent;
use Hyvor\Internal\Component\Component;

class OrgRestored extends AbstractEvent
{

    public function __construct(
        private int $organizationId
    ) {}

    public function getOrganizationId(): int
    {
        return $this->organizationId;
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
