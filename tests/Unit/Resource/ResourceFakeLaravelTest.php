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
        $resource = ResourceFake::enable();
        $resource->register(2, 10);

        $resource->assertRegistered(2, 10);

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Resource not registered for user 3 and resource 10');
        $resource->assertRegistered(3, 10);

        // with at
        $time = Carbon::parse('2021-01-01 12:00:00');
        $resource->register(2, 11, $time);
        $resource->assertRegistered(2, 11, $time);
    }

    public function testResourceIdWrong(): void
    {
        $resource = ResourceFake::enable();
        $resource->register(2, 10);

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Resource not registered for user 2 and resource 11');
        $resource->assertRegistered(2, 11);
    }

    public function testRegistersAtWrong(): void
    {
        $resource = ResourceFake::enable();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Resource not registered for user 2 and resource 11 at 2021-01-01 12:00:01');
        $resource->assertRegistered(2, 11, Carbon::parse('2021-01-01 12:00:01'));
    }

    public function testDeletes(): void
    {
        $resource = ResourceFake::enable();
        $resource->delete(2);

        $resource->assertDeleted(2);

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Resource not deleted: 3');
        $resource->assertDeleted(3);
    }

}
