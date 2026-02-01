<?php

namespace Hyvor\Internal\Bundle\Comms\Event\ToCore\License;

use Hyvor\Internal\Bundle\Comms\Event\AbstractEvent;
use Hyvor\Internal\Component\Component;


/**
 * @extends AbstractEvent<GetLicensesResponse>
 */
class GetLicenses extends AbstractEvent
{

    /**
     * @var int[]
     */
    private array $organizationIds;

    /**
     * @param int[] $organizationIds
     */
    public function __construct(
        array $organizationIds,
        private Component $component
    ) {
        $this->organizationIds = array_unique($organizationIds);
    }

    /**
     * @return int[]
     */
    public function getOrganizationIds(): array
    {
        return $this->organizationIds;
    }

    public function getComponent(): Component
    {
        return $this->component;
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