<?php

namespace Unit\CloudApi;

use Hyvor\Internal\CloudApi\JwtSource\JwtSource;
use Hyvor\Internal\Component\Component;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(JwtSource::class)]
class JwtSourceTest extends \PHPUnit\Framework\TestCase
{

    public function test_jwt_source(): void
    {

        $forDev = JwtSource::forDeveloper('app_123');
        $this->assertSame('dev:app_123', $forDev->getSource());

        $forCloud = JwtSource::forCloud('cloud_456');
        $this->assertSame('cloud:cloud_456', $forCloud->getSource());

        $forInternal = JwtSource::forInternal(Component::CORE);
        $this->assertSame('internal:core', $forInternal->getSource());

    }

    public function test_jwt_source_from_string(): void
    {
        $fromDev = JwtSource::fromString('dev:app_123');
        $this->assertSame('dev:app_123', $fromDev->getSource());

        $fromCloud = JwtSource::fromString('cloud:cloud_456');
        $this->assertSame('cloud:cloud_456', $fromCloud->getSource());

        $fromInternal = JwtSource::fromString('internal:core');
        $this->assertSame('internal:core', $fromInternal->getSource());
    }

}
