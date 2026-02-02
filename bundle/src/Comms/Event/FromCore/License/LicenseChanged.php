<?php

namespace Hyvor\Internal\Bundle\Comms\Event\FromCore\License;

use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Bundle\Comms\Event\AbstractEvent;
use Hyvor\Internal\Component\Component;

class LicenseChanged extends AbstractEvent
{

    public function __construct(
        private int $organizationId,
        private Component $component, // this event is generally sent to this component
        private ResolvedLicense $previousLicense,
        private ResolvedLicense $newLicense,
    ) {}

    public function getOrganizationId(): int
    {
        return $this->organizationId;
    }

    public function getComponent(): Component
    {
        return $this->component;
    }

    public function getPreviousLicense(): ResolvedLicense
    {
        return $this->previousLicense;
    }

    public function getNewLicense(): ResolvedLicense
    {
        return $this->newLicense;
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
