<?php

namespace Hyvor\Internal\Tests\Unit\InternalApi;

use Hyvor\Internal\InternalApi\Testing\CallsInternalApi;
use Hyvor\Internal\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CallsInternalApi::class)]
class CallsInternalApiLaravelTest extends LaravelTestCase
{

    use CallsInternalApi;

    public function testCallsPost(): void
    {
        $response = $this->internalApi(
            '/internal-api-testing-test-route-post',
            [
                'test' => 'post'
            ]
        );

        $response->assertOk();
        $response->assertJsonPath('test', 'post');
    }
}
