<?php

namespace Hyvor\Internal\Component;

use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\License\CoreLicense;
use Hyvor\Internal\Billing\License\License;
use Hyvor\Internal\Billing\License\Plan\BlogsPlan;
use Hyvor\Internal\Billing\License\Plan\CorePlan;
use Hyvor\Internal\Billing\License\Plan\PlanAbstract;
use Hyvor\Internal\Billing\License\Plan\PostPlan;
use Hyvor\Internal\Billing\License\Plan\TalkPlan;
use Hyvor\Internal\Billing\License\PostLicense;
use Hyvor\Internal\Billing\License\TalkLicense;

enum Component: string
{
    case CORE = 'core';
    case TALK = 'talk';
    case BLOGS = 'blogs';
    case POST = 'post';

    public function name(): string
    {
        return match ($this) {
            self::CORE => 'HYVOR',
            self::TALK => 'Hyvor Talk',
            self::BLOGS => 'Hyvor Blogs',
            self::POST => 'Hyvor Post',
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
        };
    }

    public function plans(): PlanAbstract
    {
        $class = match ($this) {
            self::CORE => CorePlan::class,
            self::TALK => TalkPlan::class,
            self::BLOGS => BlogsPlan::class,
            self::POST => PostPlan::class,
        };

        return new $class();
    }

}
