<?php

namespace Hyvor\Internal\Tests\Unit\Billing;

use Hyvor\Internal\Billing\License\Plan\BlogsPlan;
use Hyvor\Internal\Billing\License\Plan\CorePlan;
use Hyvor\Internal\Billing\License\Plan\Plan;
use Hyvor\Internal\Billing\License\Plan\PlanAbstract;
use Hyvor\Internal\Billing\License\Plan\TalkPlan;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Plan::class)]
#[CoversClass(PlanAbstract::class)]
#[CoversClass(BlogsPlan::class)]
#[CoversClass(CorePlan::class)]
#[CoversClass(TalkPlan::class)]
class PlanTest extends TestCase
{

    public function testBlogsPlan(): void
    {
        $plan = new BlogsPlan();
        $this->assertEquals(2, $plan->getCurrentVersion());

        $this->assertCount(3, $plan->getCurrentPlans());

        $starter = $plan->getPlan('starter');
        $this->assertEquals(2, $starter->version);
        $this->assertEquals("starter", $starter->name);
        $this->assertEquals(12, $starter->monthlyPrice);
        $this->assertEquals('Starter', $starter->nameReadable);
        $this->assertEquals(2, $starter->license->users);

        $triedPlan = $plan->tryGetPlan('tried');
        $this->assertNull($triedPlan);
    }

    public function testTalkPlan(): void
    {
        $plan = new TalkPlan();
        $this->assertEquals(1, $plan->getCurrentVersion());
        // dd($plan->getCurrentPlans());
    }

    public function testCorePlan(): void
    {
        $plan = new CorePlan();
        $this->assertEquals(1, $plan->getCurrentVersion());
        // add more tests when needed
    }

}
