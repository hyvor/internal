<?php

namespace Hyvor\Internal\Billing\License;

final class FortguardLicense extends License
{

    public function __construct(
        public int $credits
    ) {}

    public static function trial(): static
    {
        return new self(credits: 2000);
    }
}
