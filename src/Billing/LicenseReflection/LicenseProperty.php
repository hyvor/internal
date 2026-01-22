<?php

namespace Hyvor\Internal\Billing\LicenseReflection;

use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
readonly class LicenseProperty
{

    public function __construct(
        public string $name,
        public LicensePropertyType $type,
        public int|bool $default,
    ) {
    }

}