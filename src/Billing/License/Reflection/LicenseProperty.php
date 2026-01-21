<?php

namespace Hyvor\Internal\Billing\License\Reflection;

readonly class LicenseProperty
{

    public function __construct(
        public string $name,
        public LicensePropertyType $type,
        public int|bool $default,
    ) {
    }

}