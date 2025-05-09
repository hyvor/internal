<?php

namespace Hyvor\Internal\Tests\Unit\Component;

use Hyvor\Internal\Component\Logo;
use Hyvor\Internal\Tests\SymfonyTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Logo::class)]
class LogoSymfonyTest extends SymfonyTestCase
{
    use LogoTestTrait;

    protected function getLogo(): Logo
    {
        $logo = $this->container->get(Logo::class);
        assert($logo instanceof Logo);
        return $logo;
    }
}