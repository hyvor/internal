<?php

namespace Hyvor\Internal\Component;

use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\License\CoreLicense;
use Hyvor\Internal\Billing\License\License;
use Hyvor\Internal\Billing\License\Plan\BlogsPlan;
use Hyvor\Internal\Billing\License\Plan\CorePlan;
use Hyvor\Internal\Billing\License\Plan\PlanAbstract;
use Hyvor\Internal\Billing\License\Plan\TalkPlan;
use Hyvor\Internal\Billing\License\TalkLicense;

enum Component: string
{
    case CORE = 'core';
    case TALK = 'talk';
    case BLOGS = 'blogs';

    public function name(): string
    {
        return match ($this) {
            self::CORE => 'HYVOR',
            self::TALK => 'Hyvor Talk',
            self::BLOGS => 'Hyvor Blogs',
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
        };
    }

    public function plans(): PlanAbstract
    {
        $class = match ($this) {
            self::CORE => CorePlan::class,
            self::TALK => TalkPlan::class,
            self::BLOGS => BlogsPlan::class,
        };

        return new $class();
    }

}
