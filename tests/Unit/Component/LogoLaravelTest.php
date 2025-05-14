<?php

namespace Hyvor\Internal\Tests\Unit\Component;

use Hyvor\Internal\Component\Component;
use Hyvor\Internal\Component\Logo;
use Hyvor\Internal\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Logo::class)]
class LogoLaravelTest extends LaravelTestCase
{
    use LogoTestTrait;

    protected function getLogo(): Logo
    {
        return app(Logo::class);
    }
}