<?php

namespace Hyvor\Internal\Tests\Unit\InternalApi;

use Hyvor\Internal\Http\Exceptions\HttpException;
use Hyvor\Internal\InternalApi\Middleware\InternalApiMiddleware;
use Hyvor\Internal\Tests\LaravelTestCase;
use Illuminate\Support\Facades\Crypt;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(InternalApiMiddleware::class)]
class InternalApiMiddlewareLaravelTest extends LaravelTestCase
{
    public function testDecryptsMessageAndSetsRequestAttributes(): void
    {
        $request = new \Illuminate\Http\Request();
        $request->replace([
            'message' => Crypt::encryptString(
                (string)json_encode([
                    'data' => [
                        'user_id' => 123,
                        'ids' => [1, 2, 3],
                    ],
                    'timestamp' => time()
                ])
            )
        ]);
        $request->headers->set('X-Internal-Api-To', 'core');

        $middleware = new InternalApiMiddleware();
        $middleware->handle($request, function ($request) {
            $this->assertEquals(123, $request->user_id);
            $this->assertEquals([1, 2, 3], $request->ids);
        });
    }

    public function testRejectsInvalidToComponent(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Invalid to component');
        $this->expectExceptionCode(403);

        $request = new \Illuminate\Http\Request();
        $request->headers->set('X-Internal-Api-To', 'talk');

        $middleware = new InternalApiMiddleware();
        $middleware->handle($request, fn() => null);
    }

    public function testOnMissingMessage(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Invalid message');

        $request = new \Illuminate\Http\Request();
        $request->headers->set('X-Internal-Api-To', 'core');

        $middleware = new InternalApiMiddleware();
        $middleware->handle($request, fn() => null);
    }

    public function testOnMissingTimestamp(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Invalid timestamp');

        $request = new \Illuminate\Http\Request();
        $request->replace([
            'message' => Crypt::encryptString(
                (string)json_encode([
                    'data' => [
                        'user_id' => 123,
                        'ids' => [1, 2, 3],
                    ]
                ])
            )
        ]);
        $request->headers->set('X-Internal-Api-To', 'core');

        $middleware = new InternalApiMiddleware();
        $middleware->handle($request, fn() => null);
    }

    public function testOnExpiredMessage(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Expired message');

        $request = new \Illuminate\Http\Request();
        $request->replace([
            'message' => Crypt::encryptString(
                (string)json_encode([
                    'data' => [
                        'user_id' => 123,
                        'ids' => [1, 2, 3],
                    ],
                    'timestamp' => time() - 65
                ])
            )
        ]);
        $request->headers->set('X-Internal-Api-To', 'core');

        $middleware = new InternalApiMiddleware();
        $middleware->handle($request, fn() => null);
    }

    public function testOnInvalidMessage(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Failed to decrypt message');

        $request = new \Illuminate\Http\Request();
        $request->replace([
            'message' => 'invalid'
        ]);
        $request->headers->set('X-Internal-Api-To', 'core');

        $middleware = new InternalApiMiddleware();
        $middleware->handle($request, fn() => null);
    }

    public function testOnInvalidData(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Invalid data');

        $request = new \Illuminate\Http\Request();
        $request->replace([
            'message' => Crypt::encryptString('invalid')
        ]);
        $request->headers->set('X-Internal-Api-To', 'core');

        $middleware = new InternalApiMiddleware();
        $middleware->handle($request, fn() => null);
    }
}
