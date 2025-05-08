<?php

namespace Hyvor\Internal\Tests\Unit\InternalApi;

use Hyvor\Internal\InternalApi\Middleware\InternalApiFromMiddleware;
use Hyvor\Internal\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(InternalApiFromMiddleware::class)]
class InternalApiFromMiddlewareLaravelTest extends LaravelTestCase
{
    public function testDoesNotAllowMissingComponent(): void
    {
        $response = $this->get('/api/internal/internal-api-testing-test-route-from-middleware');
        $response->assertStatus(403);
        $response->assertSee('Missing from component');
    }

    public function testDoesNotAllowWrongFromComponent(): void
    {
        $response = $this->withHeader('X-Internal-Api-From', 'talk')
            ->get('/api/internal/internal-api-testing-test-route-from-middleware');
        $response->assertStatus(403);
        $response->assertSee('Invalid from component');
    }

    public function testAllowsCorrectFromComponent(): void
    {
        $response = $this->withHeader('X-Internal-Api-From', 'core')
            ->get('/api/internal/internal-api-testing-test-route-from-middleware');
        $response->assertStatus(200);
        $response->assertSee('ok');
    }
}
