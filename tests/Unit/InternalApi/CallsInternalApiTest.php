<?php

namespace Hyvor\Internal\Tests\Unit\InternalApi;

use Hyvor\Internal\InternalApi\Testing\CallsInternalApi;
use Hyvor\Internal\Tests\Case\InternalApiTestingCaseLaravel;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CallsInternalApi::class)]
class CallsInternalApiTest extends InternalApiTestingCaseLaravel
{
    public function testCallsSelf(): void
    {
        $response = $this->internalApi(
            'GET',
            '/internal-api-testing-test-route',
            [
                'test' => 'test'
            ]
        );

        $response->assertOk();
        $response->assertJsonPath('test', 'test');
    }

    public function testCallsPost(): void
    {
        $response = $this->internalApi(
            'POST',
            '/internal-api-testing-test-route-post',
            [
                'test' => 'post'
            ]
        );

        $response->assertOk();
        $response->assertJsonPath('test', 'post');
    }
}
