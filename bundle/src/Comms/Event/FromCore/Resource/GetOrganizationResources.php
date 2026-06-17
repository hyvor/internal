<?php

namespace Hyvor\Internal\Bundle\Comms\Event\FromCore\Resource;

use Hyvor\Internal\Bundle\Comms\Event\AbstractEvent;
use Hyvor\Internal\Component\Component;

/**
 * @extends AbstractEvent<GetOrganizationResourcesResponse>
 */
class GetOrganizationResources extends AbstractEvent
{

    public function __construct(
        public int $organizationId,
        public ?int $currentUserId,
        public int $limit,
        public int $offset,
        public ?string $search,
        public GetOrganizationResourcesSort $sort,
    )
    {
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
