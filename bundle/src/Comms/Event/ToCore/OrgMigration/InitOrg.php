<?php

namespace Hyvor\Internal\Bundle\Comms\Event\ToCore\OrgMigration;

use Hyvor\Internal\Bundle\Comms\Event\AbstractEvent;
use Hyvor\Internal\Component\Component;

/**
 * @extends AbstractEvent<InitOrgResponse>
 */
class InitOrg extends AbstractEvent
{

    public function __construct(
        /**
         * This is an owner of a resource
         * Core will create an organization for this user if one does not exist
         * Otherwise, will return the already created organization
         */
        public int $userId,
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