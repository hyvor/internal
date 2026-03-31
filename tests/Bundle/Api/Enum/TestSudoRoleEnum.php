<?php

namespace Hyvor\Internal\Tests\Bundle\Api\Enum;

use Hyvor\Internal\Sudo\SudoRoleInterface;

enum TestSudoRoleEnum: string implements SudoRoleInterface
{
    case SUDO = 'sudo';
    case SUPPORT = 'support';

    public function getPermissions(): array
    {
        return match ($this) {
            self::SUDO => TestSudoPermissionEnum::cases(),
            self::SUPPORT => [TestSudoPermissionEnum::ACCESS_SUDO]
        };
    }
}
