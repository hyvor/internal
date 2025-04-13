<?php

namespace Hyvor\Internal\Tests\Unit\Billing;

use Hyvor\Internal\Billing\Billing;
use Hyvor\Internal\Billing\Dto\LicenseOf;
use Hyvor\Internal\Billing\Dto\LicensesCollection;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\SubscriptionIntent;
use Hyvor\Internal\InternalApi\ComponentType;
use Hyvor\Internal\InternalApi\InternalApi;
use Hyvor\Internal\Tests\TestCase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Billing::class)]
#[CoversClass(SubscriptionIntent::class)]
#[CoversClass(LicensesCollection::class)]
#[CoversClass(LicenseOf::class)]
class BillingTest extends TestCase
{

    public function testSubscriptionIntent(): void
    {
        $billing = new Billing();
        $intent = $billing->subscriptionIntent(1, 'starter', true, ComponentType::BLOGS);

        $token = $intent['token'];

        $this->assertEquals("https://hyvor.com/account/billing/subscription?token=$token", $intent['urlNew']);
        $this->assertEquals(
            "https://hyvor.com/account/billing/subscription?token=$token&change=1",
            $intent['urlChange']
        );
    }

    public function testGetLicense(): void
    {
        $billing = new Billing();

        Http::fake([
            'https://hyvor.com/api/internal/billing/licenses*' => Http::response([
                [
                    'user_id' => 1,
                    'resource_id' => 10,
                    'license' => (new BlogsLicense())->serialize()
                ]
            ])
        ]);

        $license = $billing->license(1, 10, ComponentType::BLOGS);

        $this->assertInstanceOf(BlogsLicense::class, $license);
        $this->assertEquals(2, $license->users);

        Http::assertSent(function (Request $request) {
            $data = InternalApi::dataFromMessage($request->data()['message']);

            $this->assertCount(1, $data['of']);
            $this->assertEquals(1, $data['of'][0]['user_id']);
            $this->assertEquals(10, $data['of'][0]['resource_id']);

            $headers = $request->headers();
            $this->assertEquals('core', $headers['X-Internal-Api-To'][0]);
            $this->assertEquals('blogs', $headers['X-Internal-Api-From'][0]);

            return true;
        });
    }

    public function test_get_licenses(): void
    {
        $billing = new Billing();

        Http::fake([
            'https://hyvor.com/api/internal/billing/licenses*' => Http::response([
                [
                    'user_id' => 1,
                    'resource_id' => null,
                    'license' => (new BlogsLicense())->serialize()
                ],
                [
                    'user_id' => 2,
                    'resource_id' => null,
                    'license' => null
                ]
            ])
        ]);

        $licenses = $billing->licenses([
            new LicenseOf(1, null),
            new LicenseOf(2, null),
        ], ComponentType::BLOGS);

        $this->assertInstanceOf(LicensesCollection::class, $licenses);
        $this->assertCount(2, $licenses->all());

        $user1License = $licenses->of(1, null);
        $this->assertInstanceOf(BlogsLicense::class, $user1License);

        $user2License = $licenses->of(2, null);
        $this->assertNull($user2License);


        Http::assertSent(function (Request $request) {
            $data = InternalApi::dataFromMessage($request->data()['message']);

            $this->assertIsArray($data['of']);
            $this->assertCount(2, $data['of']);

            $this->assertEquals(1, $data['of'][0]['user_id']);
            $this->assertEquals(null, $data['of'][0]['resource_id']);
            $this->assertEquals(2, $data['of'][1]['user_id']);
            $this->assertEquals(null, $data['of'][1]['resource_id']);

            $headers = $request->headers();
            $this->assertEquals('core', $headers['X-Internal-Api-To'][0]);
            $this->assertEquals('blogs', $headers['X-Internal-Api-From'][0]);

            return true;
        });
    }

}
