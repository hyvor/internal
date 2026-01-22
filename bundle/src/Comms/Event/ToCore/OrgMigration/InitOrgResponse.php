<?php

namespace Hyvor\Internal\Bundle\Comms\Event\ToCore\OrgMigration;

class InitOrgResponse
{

    public function __construct(
        public int $orgId,
    ) {
    }

}