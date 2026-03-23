<?php

namespace Hyvor\Internal\Bundle\Comms\Event\ToCore\Organization;

use Hyvor\Internal\Bundle\Comms\Event\AbstractEvent;

class GetOrganizations extends AbstractEvent
{
    public function __construct(
        private array $organizationIds,
    ) {}

    public function getOrganizationIds(): array
    {
        return $this->organizationIds;
    }

    public function from(): array
    {
        return [];
    }
    
    
}