<?php

namespace Hyvor\Internal\Tests\Unit\Resource;

use Hyvor\Internal\InternalApi\InternalApi;
use Hyvor\Internal\Resource\Resource;
use Hyvor\Internal\Tests\SymfonyTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(Resource::class)]
class ResourceSymfonyTest extends SymfonyTestCase
{

    use ResourceTestTrait;

    protected function getResource(): Resource
    {
        /** @var Resource $resource */
        $resource = $this->container->get(Resource::class);
        return $resource;
    }

    protected function getInternalApi(): InternalApi
    {
        /** @var InternalApi $internalApi */
        $internalApi = $this->container->get(InternalApi::class);
        return $internalApi;
    }

    protected function setResponseFactory(JsonMockResponse $response): void
    {
        $httpClient = $this->container->get(HttpClientInterface::class);
        assert($httpClient instanceof MockHttpClient);
        $httpClient->setResponseFactory($response);
    }

}