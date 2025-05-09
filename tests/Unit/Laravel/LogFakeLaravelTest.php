<?php

namespace Hyvor\Internal\Tests\Unit\Laravel;

use Hyvor\Internal\Laravel\LogFake\LogFake;
use Hyvor\Internal\Tests\LaravelTestCase;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LogFake::class)]
class LogFakeLaravelTest extends LaravelTestCase
{

    public function testDoesNotAllowOnNonTestingEnv(): void
    {
        config(['app.env' => 'production']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot fake log in non-testing environment');

        LogFake::enable();
    }

    public function testLogFake(): void
    {
        LogFake::enable();
        Log::info('Some info', ['id' => 1]);
        LogFake::assertLogged('info', 'Some info', ['id' => 1]);
    }

    public function testLogFakeCallback(): void
    {
        LogFake::enable();
        Log::alert('Some alert', ['id' => 1]);
        LogFake::assertLoggedCallback(function (string $level, string $message, array $data) {
            $this->assertEquals('alert', $level);
            $this->assertEquals('Some alert', $message);
            $this->assertEquals(['id' => 1], $data);

            return true;
        });
    }

}
