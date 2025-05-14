<?php

namespace Hyvor\Internal\Tests\Unit\Billing;

use Hyvor\Internal\Billing\Billing;
use Hyvor\Internal\Billing\Dto\LicenseOf;
use Hyvor\Internal\Billing\Dto\LicensesCollection;
use Hyvor\Internal\Billing\SubscriptionIntent;
use Hyvor\Internal\InternalApi\InternalApi;
use Hyvor\Internal\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(Billing::class)]
#[CoversClass(SubscriptionIntent::class)]
#[CoversClass(LicensesCollection::class)]
#[CoversClass(LicenseOf::class)]
class BillingLaravelTest extends LaravelTestCase
{

    use BillingTestTrait;

    protected function getBilling(): Billing
    {
        return app(Billing::class);
    }

    protected function setHttpResponse(MockResponse $response): void
    {
        app()->singleton(HttpClientInterface::class, fn() => new MockHttpClient($response));
    }

    protected function getInternalApi(): InternalApi
    {
        return app(InternalApi::class);
    }
}
