<?php

namespace Hyvor\Internal\Tests\Unit\Component;

use Hyvor\Internal\Billing\License\Plan\BlogsPlan;
use Hyvor\Internal\Billing\License\Plan\CorePlan;
use Hyvor\Internal\Billing\License\Plan\PostPlan;
use Hyvor\Internal\Billing\License\Plan\TalkPlan;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Component::class)]
class ComponentTest extends LaravelTestCase
{

    public function testName(): void
    {
        $this->assertEquals('HYVOR', Component::CORE->name());
        $this->assertEquals('Hyvor Talk', Component::TALK->name());
        $this->assertEquals('Hyvor Blogs', Component::BLOGS->name());
        $this->assertEquals('Hyvor Post', Component::POST->name());
    }

    public function testPlans(): void
    {
        $plans = [
            [Component::CORE, CorePlan::class],
            [Component::TALK, TalkPlan::class],
            [Component::BLOGS, BlogsPlan::class],
            [Component::POST, PostPlan::class],
        ];

        foreach ($plans as $plan) {
            $this->assertInstanceOf($plan[1], $plan[0]->plans());
        }
    }

}
