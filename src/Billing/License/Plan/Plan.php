<?php

namespace Hyvor\Internal\Billing\License\Plan;

use Hyvor\Internal\Billing\License\License;

/**
 * @template T of License = License
 */
class Plan
{

    public string $nameReadable;

    public function __construct(
        public int $version,
        public string $name,
        public float $monthlyPrice,
        /**
         * @var T
         */
        public License $license,

        /**
         * If the readable name is simply capitalized $name, you can leave this null.
         */
        ?string $nameReadable = null,

        /**
         * Can be used to group plans
         * ex: HT has "premium" group and "Premium 100k", "Premium 1M" plans in it.
         * Usually, if one plan has a group, all plans should have a group
         */
        public ?string $group = null,
    ) {
        $this->nameReadable = $nameReadable ?? ucfirst($this->name);
    }

}
