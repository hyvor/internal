<?php

namespace Hyvor\Internal\Tests\Unit\Billing;

use Hyvor\Internal\Billing\Billing;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\InternalApi\InternalApi;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\HttpClient\Response\MockResponse;

trait BillingTestTrait
{

    abstract protected function getBilling(): Billing;

    abstract protected function setHttpResponse(MockResponse $response): void;

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

    public function testGetLicense(): void
    {
        $mockResponse = new JsonMockResponse([
            [
                'user_id' => 1,
                'resource_id' => 10,
                'license' => BlogsLicense::trial()->serialize()
            ]
        ]);
        $this->setHttpResponse($mockResponse);

        $billing = $this->getBilling();
        $license = $billing->license(1, Component::BLOGS);

        $this->assertInstanceOf(BlogsLicense::class, $license);
        $this->assertEquals(2, $license->users);

        // HTTP Request
        $data = $this->getInternalApi()->dataFromMockResponse($mockResponse);

        $this->assertCount(1, $data['of']);
        $this->assertEquals(1, $data['of'][0]['user_id']);
        $this->assertEquals(10, $data['of'][0]['resource_id']);

        $headers = $mockResponse->getRequestOptions()['headers'];
        $this->assertContains('X-Internal-Api-To: core', $headers);
        $this->assertContains('X-Internal-Api-From: blogs', $headers);
    }

    public function test_get_licenses(): void
    {
        $mockResponse = new JsonMockResponse([
            [
                'user_id' => 1,
                'resource_id' => null,
                'license' => BlogsLicense::trial()->serialize()
            ],
            [
                'user_id' => 2,
                'resource_id' => null,
                'license' => null
            ]
        ]);
        $this->setHttpResponse($mockResponse);

        $billing = $this->getBilling();
        $licenses = $billing->licenses([1, 2], Component::BLOGS);

        $this->assertCount(2, $licenses);

        $user1License = $licenses[1];
        $this->assertInstanceOf(BlogsLicense::class, $user1License);

        $user2License = $licenses[2];

        // HTTP Request
        $data = $this->getInternalApi()->dataFromMockResponse($mockResponse);
        $this->assertIsArray($data['of']);
        $this->assertCount(2, $data['of']);

        $this->assertEquals(1, $data['of'][0]['user_id']);
        $this->assertEquals(null, $data['of'][0]['resource_id']);
        $this->assertEquals(2, $data['of'][1]['user_id']);
        $this->assertEquals(null, $data['of'][1]['resource_id']);

        $headers = $mockResponse->getRequestOptions()['headers'];
        $this->assertContains('X-Internal-Api-To: core', $headers);
        $this->assertContains('X-Internal-Api-From: blogs', $headers);
    }


}
