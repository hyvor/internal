<?php

namespace Hyvor\Internal\Bundle\Comms\Event\ToCore\OrgMigration;

use Hyvor\Internal\Bundle\Comms\Event\AbstractEvent;
use Hyvor\Internal\Component\Component;

class EnsureMembers extends AbstractEvent
{

    public function __construct(
        public int $orgId,
        /**
         * Users who should be a member of the organization
         * This ensures that all moderators, admins, etc. of a resource are part of the organization
         * @var int[]
         */
        public array $userIds,
    ) {
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