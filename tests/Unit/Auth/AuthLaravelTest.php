<?php

namespace Hyvor\Internal\Tests\Unit\Auth;

use Hyvor\Internal\Auth\Auth;
use Hyvor\Internal\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Auth::class)]
class AuthLaravelTest extends LaravelTestCase
{

    use AuthTestTrait;

    protected function getContainer(): \Illuminate\Container\Container|\Symfony\Component\DependencyInjection\Container
    {
        return app();
    }
}
