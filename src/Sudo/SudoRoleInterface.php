<?php

namespace Hyvor\Internal\Sudo;

interface SudoRoleInterface
{

    /**
     * @return array<\BackedEnum & SudoPermissionInterface>
     */
    public function getPermissions(): array;

}
