<?php

namespace Hyvor\Internal\Bundle\Comms\Event\ToCore\Organization;

use Hyvor\Internal\Auth\Dto\Organization;

readonly class GetOrganizationsResponse
{
    public function __construct(
        /** @var Organization[] $organizations */
        private array $organizations,
    ) {}

    /**
     * @return Organization[]
     */
    public function getOrganizations(): array
    {
        return $this->organizations;
    }
}