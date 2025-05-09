<?php

namespace Hyvor\Internal\Tests\Unit\Resource;

use Carbon\Carbon;
use Hyvor\Internal\InternalApi\InternalApi;
use Hyvor\Internal\Resource\Resource;
use Hyvor\Internal\Tests\LaravelTestCase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Resource::class)]
class ResourceLaravelTest extends LaravelTestCase
{

    public function testRegister(): void
    {
        Http::fake([
            'https://hyvor.com/api/internal/resource/register' => Http::response()
        ]);

        $resource = new Resource();
        $resource->register(10, 20);

        Http::assertSent(function (Request $request) {
            $data = InternalApi::dataFromMessage($request->data()['message']);
            $this->assertEquals(10, $data['user_id']);
            $this->assertEquals(20, $data['resource_id']);
            $this->assertEquals(null, $data['at']);

            $this->assertEquals('POST', $request->method());

            return true;
        });
    }

    public function testRegisterWithTime(): void
    {
        Http::fake([
            'https://hyvor.com/api/internal/resource/register' => Http::response()
        ]);

        $resource = new Resource();
        $time = Carbon::parse('2021-01-01 12:00:00');
        $resource->register(10, 20, $time);

        Http::assertSent(function (Request $request) use ($time) {
            $data = InternalApi::dataFromMessage($request->data()['message']);
            $this->assertEquals(10, $data['user_id']);
            $this->assertEquals(20, $data['resource_id']);
            $this->assertEquals($time->timestamp, $data['at']);

            $this->assertEquals('POST', $request->method());

            return true;
        });
    }

    public function testDelete(): void
    {
        Http::fake([
            'https://hyvor.com/api/internal/resource/delete' => Http::response()
        ]);

        $resource = new Resource();
        $resource->delete(25);

        Http::assertSent(function (Request $request) {
            $data = InternalApi::dataFromMessage($request->data()['message']);
            $this->assertEquals(25, $data['resource_id']);
            $this->assertEquals('POST', $request->method());
            return true;
        });
    }

}
