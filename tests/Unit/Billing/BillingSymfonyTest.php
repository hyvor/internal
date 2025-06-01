<?php

namespace Hyvor\Internal\Tests\Unit\Billing;

use Hyvor\Internal\Billing\Billing;
use Hyvor\Internal\Billing\Dto\LicenseOf;
use Hyvor\Internal\Billing\Dto\LicensesCollection;
use Hyvor\Internal\Billing\SubscriptionIntent;
use Hyvor\Internal\InternalApi\InternalApi;
use Hyvor\Internal\Tests\SymfonyTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(Billing::class)]
#[CoversClass(SubscriptionIntent::class)]
#[CoversClass(LicensesCollection::class)]
#[CoversClass(LicenseOf::class)]
class BillingSymfonyTest extends SymfonyTestCase
{

    use BillingTestTrait;

    protected function getBilling(): Billing
    {
        $billing = $this->container->get(Billing::class);
        assert($billing instanceof Billing);
        return $billing;
    }

    protected function setHttpResponse(MockResponse $response): void
    {
        $currentClient = $this->container->get(HttpClientInterface::class);
        assert($currentClient instanceof MockHttpClient);
        $currentClient->setResponseFactory($response);
    }

    protected function getInternalApi(): InternalApi
    {
        $internalApi = $this->container->get(InternalApi::class);
        assert($internalApi instanceof InternalApi);
        return $internalApi;
    }
}