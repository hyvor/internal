<?php

namespace Hyvor\Internal\Billing\License;

use Hyvor\Internal\Billing\License\Property\LicenseProperty;

final class FortGuardLicense extends License
{
    public function __construct(
        public int $credits
    ) {
    }

    public static function properties(): array
    {
        return [
            LicenseProperty::int('credits')
                ->name('Credits')
                ->description('Number of credits allocated for a month')
        ];
    }

    public static function trial(): static
    {
        return new self(
            credits: 2000
        );
    }
}
