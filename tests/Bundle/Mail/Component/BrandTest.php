<?php

namespace Hyvor\Internal\Tests\Bundle\Mail\Component;

use Hyvor\Internal\Bundle\Mail\Component\Brand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Brand::class)]
class BrandTest extends TestCase
{

    public function test_brand(): void
    {
        $brand = new Brand();
        $brand->component = 'blogs';

        $this->assertStringContainsString('data:image/svg+xml;base64', $brand->getImage());
        $this->assertStringContainsString('Hyvor Blogs', $brand->getName());
    }

}