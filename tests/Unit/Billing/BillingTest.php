<?php

namespace Hyvor\Internal\Tests\Unit\Billing;

use Hyvor\Internal\Billing\Billing;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\SubscriptionIntent;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\InternalApi\InternalApi;
use Hyvor\Internal\Tests\TestCase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Billing::class)]
#[CoversClass(SubscriptionIntent::class)]
class BillingTest extends TestCase
{

    public function testSubscriptionIntent(): void
    {
        $billing = new Billing();
        $intent = $billing->subscriptionIntent(1, 'starter', true, Component::BLOGS);

        $token = $intent['token'];
        $this->assertIsString($token);

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
            'https://hyvor.com/api/internal/billing/license*' => Http::response([
                'license' => (new BlogsLicense())->serialize()
            ])
        ]);

        $license = $billing->license(1, 10, Component::BLOGS);

        $this->assertInstanceOf(BlogsLicense::class, $license);
        $this->assertEquals(2, $license->users);

        Http::assertSent(function (Request $request) {
            $data = InternalApi::dataFromMessage($request->data()['message']);

            $this->assertEquals(1, $data['user_id']);
            $this->assertEquals(10, $data['resource_id']);

            $headers = $request->headers();
            $this->assertEquals('core', $headers['X-Internal-Api-To'][0]);
            $this->assertEquals('blogs', $headers['X-Internal-Api-From'][0]);

            return true;
        });
    }

}
