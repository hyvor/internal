<?php

namespace Hyvor\Internal\Billing\License;

final class RelayLicense extends License
{

    public static function trial(): static
    {
        return new self();
    }

}
