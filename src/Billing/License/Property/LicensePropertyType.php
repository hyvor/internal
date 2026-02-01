<?php

namespace Hyvor\Internal\Billing\License\Property;

enum LicensePropertyType: string
{

    case BOOL = 'bool';
    case INT = 'int';

    public function default(): int|bool
    {
        return match ($this) {
            self::BOOL => false,
            self::INT => 0,
        };
    }

}
