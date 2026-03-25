<?php

namespace Hyvor\Internal\Bundle\Api;

use Hyvor\Internal\Sudo\SudoPermissionInterface;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS)]
readonly class SudoPermissionRequired
{

    public function __construct(
        private SudoPermissionInterface $permission
    ) {}

    public function getPermission(): SudoPermissionInterface
    {
        return $this->permission;
    }

}
