<?php

namespace Hyvor\Internal\Billing\License;

final class CoreLicense extends License
{

    public static function trial(): static
    {
        return new self();
    }
}
