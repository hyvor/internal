<?php

namespace Hyvor\Internal\Bundle\Comms\Event\ToCore\Organization;

use Hyvor\Internal\Auth\Dto\Organization;

readonly class GetOrganizationsResponse
{
    public function __construct(
        /** @var array<int, Organization> $organizations */
        private array $organizations,
    ) {}

    /**
     * @return array<int, Organization> indexed by id
     */
    public function getOrganizations(): array
    {
        return $this->organizations;
    }
}
