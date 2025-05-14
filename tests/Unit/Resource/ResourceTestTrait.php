<?php

namespace Hyvor\Internal\Tests\Unit\Resource;

use Carbon\Carbon;
use Hyvor\Internal\InternalApi\InternalApi;
use Hyvor\Internal\Resource\Resource;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

trait ResourceTestTrait
{

    abstract protected function getResource(): Resource;

    abstract protected function getInternalApi(): InternalApi;

    abstract protected function setResponseFactory(JsonMockResponse $response): void;

    public function testRegister(): void
    {
        $response = new JsonMockResponse();
        $this->setResponseFactory($response);

        $resource = $this->getResource();
        $resource->register(10, 20);

        $this->assertSame('https://hyvor.internal/api/internal/resource/register', $response->getRequestUrl());
        $this->assertSame('POST', $response->getRequestMethod());

        $data = $this->getInternalApi()->dataFromMockResponse($response);
        $this->assertEquals(10, $data['user_id']);
        $this->assertEquals(20, $data['resource_id']);
        $this->assertEquals(null, $data['at']);
    }

    public function testRegisterWithTime(): void
    {
        $response = new JsonMockResponse();
        $this->setResponseFactory($response);

        $resource = $this->getResource();
        $time = Carbon::parse('2021-01-01 12:00:00');
        $resource->register(10, 20, $time);

        $this->assertSame('https://hyvor.internal/api/internal/resource/register', $response->getRequestUrl());
        $this->assertSame('POST', $response->getRequestMethod());

        $data = $this->getInternalApi()->dataFromMockResponse($response);
        $this->assertEquals(10, $data['user_id']);
        $this->assertEquals(20, $data['resource_id']);
        $this->assertEquals($time->timestamp, $data['at']);
    }

    public function testDelete(): void
    {
        $response = new JsonMockResponse();
        $this->setResponseFactory($response);

        $resource = $this->getResource();
        $resource->delete(25);

        $this->assertSame('https://hyvor.internal/api/internal/resource/delete', $response->getRequestUrl());
        $this->assertSame('POST', $response->getRequestMethod());

        $data = $this->getInternalApi()->dataFromMockResponse($response);
        $this->assertEquals(25, $data['resource_id']);
    }

}