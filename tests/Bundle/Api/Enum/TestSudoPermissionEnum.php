<?php

namespace Hyvor\Internal\Tests\Bundle\Api\Enum;

use Hyvor\Internal\Sudo\SudoPermissionInterface;

enum TestSudoPermissionEnum: string implements SudoPermissionInterface
{
    case ACCESS_SUDO = 'access_sudo';
    case DELETE_EVERYTHING = 'delete_everything';
}
