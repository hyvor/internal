<?php

namespace Hyvor\Internal\Billing\Dto;


readonly class LicenseOf
{

    public function __construct(
        public int $userId,
        public ?int $resourceId = null,
    ) {
    }

    /**
     * @return array{user_id: int, resource_id: ?int}
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'resource_id' => $this->resourceId
        ];
    }

}