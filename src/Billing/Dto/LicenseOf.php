<?php

namespace Hyvor\Internal\Billing\Dto;


readonly class LicenseOf
{

    public function __construct(
        public int $organizationId,
        public ?int $resourceId = null,
    ) {
    }

    /**
     * @return array{organization_id: int, resource_id: ?int}
     */
    public function toArray(): array
    {
        return [
            'organization_id' => $this->organizationId,
            'resource_id' => $this->resourceId
        ];
    }

}