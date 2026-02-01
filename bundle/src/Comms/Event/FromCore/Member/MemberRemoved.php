<?php

namespace Hyvor\Internal\Bundle\Comms\Event\FromCore\Member;

use Hyvor\Internal\Bundle\Comms\Event\AbstractEvent;
use Hyvor\Internal\Component\Component;

class MemberRemoved extends AbstractEvent
{

    public function __construct(
        private int $organizationId,
        private int $userId
    ) {}

    public function getOrganizationId(): int
    {
        return $this->organizationId;
    }

    public function getUserId(): int
    {
        return $this->userId;
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
