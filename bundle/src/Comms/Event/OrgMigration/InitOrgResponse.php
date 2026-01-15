<?php

namespace Hyvor\Internal\Bundle\Comms\Event\OrgMigration;

class InitOrgResponse
{

    public function __construct(
        public int $orgId,
    ) {
    }

}