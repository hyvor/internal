<?php

namespace Hyvor\Internal\Component;

use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\License\CoreLicense;
use Hyvor\Internal\Billing\License\License;
use Hyvor\Internal\Billing\License\Plan\BlogsPlan;
use Hyvor\Internal\Billing\License\Plan\CorePlan;
use Hyvor\Internal\Billing\License\Plan\PlanAbstract;
use Hyvor\Internal\Billing\License\Plan\PostPlan;
use Hyvor\Internal\Billing\License\Plan\RelayPlan;
use Hyvor\Internal\Billing\License\Plan\TalkPlan;
use Hyvor\Internal\Billing\License\PostLicense;
use Hyvor\Internal\Billing\License\RelayLicense;
use Hyvor\Internal\Billing\License\TalkLicense;

enum Component: string
{
    case CORE = 'core';
    case TALK = 'talk';
    case BLOGS = 'blogs';
    case POST = 'post';
    case RELAY = 'relay';

    public function name(): string
    {
        return match ($this) {
            self::CORE => 'HYVOR',
            self::TALK => 'Hyvor Talk',
            self::BLOGS => 'Hyvor Blogs',
            self::POST => 'Hyvor Post',
            self::RELAY => 'Hyvor Relay',
        };
    }

    /**
     * @return class-string<License>
     */
    public function license(): string
    {
        return match ($this) {
            self::CORE => CoreLicense::class,
            self::TALK => TalkLicense::class,
            self::BLOGS => BlogsLicense::class,
            self::POST => PostLicense::class,
            self::RELAY => RelayLicense::class,
        };
    }

    public function plans(): PlanAbstract
    {
        $class = match ($this) {
            self::CORE => CorePlan::class,
            self::TALK => TalkPlan::class,
            self::BLOGS => BlogsPlan::class,
            self::POST => PostPlan::class,
            self::RELAY => RelayPlan::class,
        };

        return new $class();
    }

    public function selfHostable(): bool
    {
        return match ($this) {
            self::RELAY => true,
            default => false,
        };
    }

}
