<?php

namespace Hyvor\Internal\Tests\Unit\Resource;

use Hyvor\Internal\InternalApi\InternalApi;
use Hyvor\Internal\Resource\Resource;
use Hyvor\Internal\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(Resource::class)]
class ResourceLaravelTest extends LaravelTestCase
{

    use ResourceTestTrait;

    protected function getResource(): Resource
    {
        return app(Resource::class);
    }

    protected function getInternalApi(): InternalApi
    {
        return app(InternalApi::class);
    }

    protected function setHttpClient(MockHttpClient $client): void
    {
        app()->singleton(HttpClientInterface::class, fn() => $client);
    }

    protected function setResponseFactory(JsonMockResponse $response): void
    {
        app()->singleton(HttpClientInterface::class, fn() => new MockHttpClient($response));
    }


}
