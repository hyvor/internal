<?php

namespace Hyvor\Internal\Tests\Unit\Billing;

use Hyvor\Internal\Billing\Billing;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicenseType;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\License\GetLicenses;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\License\GetLicensesResponse;
use Hyvor\Internal\Bundle\Comms\MockComms;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\InternalApi\InternalApi;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\HttpClient\Response\MockResponse;

trait BillingTestTrait
{

    abstract protected function getBilling(): Billing;

    abstract protected function getInternalApi(): InternalApi;

    public function testSubscriptionIntent(): void
    {
        $billing = $this->getBilling();
        $intent = $billing->subscriptionIntent(1, 'starter', true, Component::BLOGS);


        $this->assertStringContainsString(
            "https://hyvor.com/account/billing/subscription?intent=", $intent['urlNew']
        );
        $this->assertStringContainsString(
            "https://hyvor.com/account/billing/subscription?intent=",
            $intent['urlChange']
        );
        $this->assertStringContainsString(
            "&change=1",
            $intent['urlChange']
        );
    }

    abstract function setComms(MockComms $comms): void;

    public function testGetLicense(): void
    {
        /** @var MockComms $mockComms */
        $mockComms = $this->getContainer()->get(MockComms::class);
        $mockComms->addResponse(GetLicenses::class, new GetLicensesResponse(
            [
                1 => new ResolvedLicense(ResolvedLicenseType::TRIAL, BlogsLicense::trial())
            ]
        ));
        $this->setComms($mockComms);

        $billing = $this->getBilling();
        $license = $billing->license(1, Component::BLOGS);

        $this->assertInstanceOf(BlogsLicense::class, $license->license);
        $this->assertEquals(2, $license->license->users);

        $mockComms->assertSent(GetLicenses::class, Component::CORE, eventValidator: function ($event) {
            $this->assertEquals([1], $event->getOrganizationIds());
            $this->assertEquals(Component::BLOGS, $event->getComponent());
        });
    }

    public function test_get_licenses(): void
    {

        /** @var MockComms $mockComms */
        $mockComms = $this->getContainer()->get(MockComms::class);
        $mockComms->addResponse(GetLicenses::class, new GetLicensesResponse(
            [
                1 => new ResolvedLicense(ResolvedLicenseType::TRIAL, BlogsLicense::trial()),
                2 => new ResolvedLicense(ResolvedLicenseType::NONE)
            ]
        ));
        $this->setComms($mockComms);

        $billing = $this->getBilling();
        $licenses = $billing->licenses([1, 2], Component::BLOGS);

        $this->assertCount(2, $licenses);

        $org1License = $licenses[1];
        $this->assertInstanceOf(BlogsLicense::class, $org1License->license);

        $org2License = $licenses[2];
        $this->assertNull($org2License->license);

        $mockComms->assertSent(GetLicenses::class, Component::CORE, eventValidator: function ($event) {
            $this->assertEquals([1, 2], $event->getOrganizationIds());
            $this->assertEquals(Component::BLOGS, $event->getComponent());
        });
    }


}
