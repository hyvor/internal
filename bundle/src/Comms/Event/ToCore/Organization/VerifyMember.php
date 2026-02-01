<?php

namespace Hyvor\Internal\Bundle\Comms\Event\ToCore\Organization;

use Hyvor\Internal\Bundle\Comms\Event\AbstractEvent;
use Hyvor\Internal\Component\Component;

/**
 * @extends AbstractEvent<VerifyMemberResponse>
 */
class VerifyMember extends AbstractEvent {

    public function __construct(
        private int $organizationId,
        private int $userId,
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
        return [];
    }

    public function to(): array
    {
        return [Component::CORE];
    }
}
