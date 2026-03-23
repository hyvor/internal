<?php

namespace Hyvor\Internal\Bundle\Comms\Event\ToCore\Organization;

use Hyvor\Internal\Bundle\Comms\Event\AbstractEvent;
use Hyvor\Internal\Component\Component;

class GetOrganizations extends AbstractEvent
{
    public function __construct(
        /** @var int[] $organizationIds */
        private array $organizationIds,
        private bool $includeBillingInfo = false,
        private bool $includeCreatedUser = false
    ) {}

    /**
     * @return int[]
     */
    public function getOrganizationIds(): array
    {
        return $this->organizationIds;
    }
    
    public function includeBillingInfo(): bool
    {
        return $this->includeBillingInfo;
    }

    public function includeCreatedUser(): bool
    {
        return $this->includeCreatedUser;
    }

    public function to(): array
    {
        return [Component::CORE];
    }

    public function from(): array
    {
        return [];
    }
    
    
}