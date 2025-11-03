<?php

namespace Hyvor\Internal\Tests\Unit\Component;

use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\License\CoreLicense;
use Hyvor\Internal\Billing\License\Plan\BlogsPlan;
use Hyvor\Internal\Billing\License\Plan\CorePlan;
use Hyvor\Internal\Billing\License\Plan\PostPlan;
use Hyvor\Internal\Billing\License\Plan\RelayPlan;
use Hyvor\Internal\Billing\License\Plan\TalkPlan;
use Hyvor\Internal\Billing\License\PostLicense;
use Hyvor\Internal\Billing\License\RelayLicense;
use Hyvor\Internal\Billing\License\TalkLicense;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Component::class)]
class ComponentTest extends LaravelTestCase
{

    public function testName(): void
    {
        $this->assertSame('HYVOR', Component::CORE->name());
        $this->assertSame('Hyvor Talk', Component::TALK->name());
        $this->assertSame('Hyvor Blogs', Component::BLOGS->name());
        $this->assertSame('Hyvor Post', Component::POST->name());
        $this->assertSame('Hyvor Relay', Component::RELAY->name());
    }

    public function testPlansLicenses(): void
    {
        $plans = [
            [Component::CORE, CorePlan::class, CoreLicense::class],
            [Component::TALK, TalkPlan::class, TalkLicense::class],
            [Component::BLOGS, BlogsPlan::class, BlogsLicense::class],
            [Component::POST, PostPlan::class, PostLicense::class],
            [Component::RELAY, RelayPlan::class, RelayLicense::class]
        ];

        foreach ($plans as $plan) {
            $this->assertInstanceOf($plan[1], $plan[0]->plans());
            $this->assertSame($plan[2], $plan[0]->license());
        }
    }

    public function test_self_hostable(): void
    {
        foreach (Component::cases() as $component) {
            $this->assertSame(
                $component === Component::RELAY,
                $component->selfHostable()
            );
        }
    }

}
