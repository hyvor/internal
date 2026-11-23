<?php

namespace Hyvor\Internal\Tests\Unit\Billing;

use Hyvor\Internal\Billing\License\Plan\BlogsPlan;
use Hyvor\Internal\Billing\License\Plan\CorePlan;
use Hyvor\Internal\Billing\License\Plan\Plan;
use Hyvor\Internal\Billing\License\Plan\PlanAbstract;
use Hyvor\Internal\Billing\License\Plan\PostPlan;
use Hyvor\Internal\Billing\License\Plan\TalkPlan;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Plan::class)]
#[CoversClass(PlanAbstract::class)]
#[CoversClass(BlogsPlan::class)]
#[CoversClass(CorePlan::class)]
#[CoversClass(TalkPlan::class)]
#[CoversClass(PostPlan::class)]
class PlanTest extends TestCase
{

    public function testBlogsPlan(): void
    {
        $plan = new BlogsPlan();
        $this->assertEquals(3, $plan->getCurrentVersion());

        $this->assertCount(4, $plan->getCurrentPlans());

        $pro = $plan->getPlan('pro');
        $this->assertEquals(3, $pro->version);
        $this->assertEquals("pro", $pro->name);
        $this->assertEquals(29, $pro->monthlyPrice);
        $this->assertEquals('Pro', $pro->nameReadable);
        $this->assertEquals(3, $pro->license->users);
        $this->assertEquals(3, $pro->license->languages);

        // older versions are still accessible
        $this->assertEquals(12, $plan->getPlan('starter', 2)->monthlyPrice);

        $triedPlan = $plan->tryGetPlan('tried');
        $this->assertNull($triedPlan);
    }

    public function testTalkPlan(): void
    {
        $plan = new TalkPlan();
        $this->assertEquals(2, $plan->getCurrentVersion());
        // dd($plan->getCurrentPlans());
    }

    public function testCorePlan(): void
    {
        $plan = new CorePlan();
        $this->assertEquals(1, $plan->getCurrentVersion());
        // add more tests when needed
    }

    public function test_post_plan(): void
    {
        $plan = new PostPlan();
        $this->assertEquals(1, $plan->getCurrentVersion());
        $this->assertCount(4, $plan->getCurrentPlans());
    }

}
