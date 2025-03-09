<?php

namespace Hyvor\Internal\Tests\Unit\InternalApi;

use Hyvor\Internal\Billing\License\Plan\BlogsPlan;
use Hyvor\Internal\Billing\License\Plan\CorePlan;
use Hyvor\Internal\Billing\License\Plan\TalkPlan;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Component::class)]
class ComponentTypeTest extends TestCase
{

    public function testName(): void
    {
        $this->assertEquals('HYVOR', Component::CORE->name());
        $this->assertEquals('Hyvor Talk', Component::TALK->name());
        $this->assertEquals('Hyvor Blogs', Component::BLOGS->name());
    }

    public function testFromConfig(): void
    {
        config(['internal.component' => 'core']);
        $this->assertEquals(Component::current(), Component::CORE);

        config(['internal.component' => 'talk']);
        $this->assertEquals(Component::current(), Component::TALK);

        config(['internal.component' => 'blogs']);
        $this->assertEquals(Component::current(), Component::BLOGS);
    }

    public function testGetCoreUrl(): void
    {
        config(['internal.instance' => 'https://hyvor.com']);
        $this->assertEquals(Component::CORE->getCoreUrl(), 'https://hyvor.com');

        config(['internal.instance' => 'https://talk.hyvor.com']);
        $this->assertEquals(Component::TALK->getCoreUrl(), 'https://hyvor.com');

        // externl
        config(['internal.instance' => 'https://hyvor.mycompany.com']);
        $this->assertEquals(Component::CORE->getCoreUrl(), 'https://hyvor.mycompany.com');

        // external product
        config(['internal.instance' => 'https://talk.hyvor.mycompany.com']);
        $this->assertEquals(Component::TALK->getCoreUrl(), 'https://hyvor.mycompany.com');
    }

    public function testGetTheUrl(): void
    {
        // core
        $this->assertEquals(Component::CORE->getUrlOf(Component::TALK), 'https://talk.hyvor.com');
        $this->assertEquals(Component::CORE->getUrlOf(Component::CORE), 'https://hyvor.com');

        // product
        $this->assertEquals(Component::TALK->getUrlOf(Component::CORE), 'https://hyvor.com');
        $this->assertEquals(Component::TALK->getUrlOf(Component::BLOGS), 'https://blogs.hyvor.com');

        // other subdomain
        config(['internal.instance' => 'https://hyvor.mycompany.com']);
        $this->assertEquals(Component::BLOGS->getUrlOf(Component::CORE), 'https://hyvor.mycompany.com');
        $this->assertEquals(Component::BLOGS->getUrlOf(Component::TALK), 'https://talk.hyvor.mycompany.com');
    }

    public function testPlans(): void
    {
        $plans = [
            [Component::CORE, CorePlan::class],
            [Component::TALK, TalkPlan::class],
            [Component::BLOGS, BlogsPlan::class],
        ];

        foreach ($plans as $plan) {
            $this->assertInstanceOf($plan[1], $plan[0]->plans());
        }
    }

}
