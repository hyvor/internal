<?php

namespace Hyvor\Internal\Billing\License\Plan;

class Meter
{
    public function __construct(
        public string $name,
        public string $property,
        public string $nameReadable,
        public float $pricePerUnit,
    ) {}
}
