<?php

namespace Hyvor\Internal\Tests\Unit\Resource;

use Carbon\Carbon;
use Hyvor\Internal\Resource\Resource;
use Hyvor\Internal\Resource\ResourceFake;
use Hyvor\Internal\Tests\LaravelTestCase;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ResourceFake::class)]
class ResourceFakeLaravelTest extends LaravelTestCase
{

    public function testRegisters(): void
    {
        ResourceFake::enable();
        $resource = app(Resource::class);
        $resource->register(2, 10);

        ResourceFake::assertRegistered(2, 10);

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Resource not registered for user 3 and resource 10');
        ResourceFake::assertRegistered(3, 10);

        // with at
        $time = Carbon::parse('2021-01-01 12:00:00');
        $resource->register(2, 11, $time);
        ResourceFake::assertRegistered(2, 11, $time);
    }

    public function testResourceIdWrong(): void
    {
        ResourceFake::enable();

        $resource = app(Resource::class);
        $resource->register(2, 10);

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Resource not registered for user 2 and resource 11');
        ResourceFake::assertRegistered(2, 11);
    }

    public function testRegistersAtWrong(): void
    {
        ResourceFake::enable();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Resource not registered for user 2 and resource 11 at 2021-01-01 12:00:01');
        ResourceFake::assertRegistered(2, 11, Carbon::parse('2021-01-01 12:00:01'));
    }

    public function testDeletes(): void
    {
        ResourceFake::enable();

        $resource = app(Resource::class);
        $resource->delete(2);

        ResourceFake::assertDeleted(2);

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Resource not deleted: 3');
        ResourceFake::assertDeleted(3);
    }

}
