<?php

namespace Hyvor\Internal\Bundle\Comms\Event\ToCore\Organization;

readonly class GetOrganizationsResponse
{
    public function __construct(
        public array $organizations,
    ) {}

    public function getOrganizations(): array
    {
        return $this->organizations;
    }
}