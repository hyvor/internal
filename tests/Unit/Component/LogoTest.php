<?php

namespace Hyvor\Internal\Tests\Unit\Component;

use Hyvor\Internal\Component\Component;
use Hyvor\Internal\Component\Logo;
use Hyvor\Internal\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class LogoTest extends TestCase
{

    public function testSvg(): void
    {
        $svg = Logo::svg(Component::BLOGS);
        $this->assertStringContainsString('<svg', $svg);
    }

    public function testUrl(): void
    {
        $url = Logo::url(Component::BLOGS);
        $this->assertEquals('https://hyvor.com/api/public/logo/blogs.svg', $url);
    }

    public static function allComponents(): mixed
    {
        return [Component::cases()];
    }

    #[DataProvider('allComponents')]
    public function testResizes(Component $component): void
    {
        $svg = Logo::svg($component, 100);
        $this->assertStringContainsString('width="100"', $svg);
        $this->assertStringContainsString('height="100"', $svg);
    }

}