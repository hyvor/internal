<?php

namespace Hyvor\Internal\Tests\Bundle\Mail\Component;

use Hyvor\Internal\Bundle\Mail\Component\Brand;
use PHPUnit\Framework\Attributes\CoversClass;
use Hyvor\Internal\Tests\SymfonyTestCase;

#[CoversClass(Brand::class)]
class BrandTest extends SymfonyTestCase
{

    public function test_brand(): void
    {
        $brand = $this->container->get(Brand::class);
        assert($brand instanceof Brand);

        $brand->component = 'blogs';

        $this->assertSame('https://hyvor.com/api/public/logo/blogs.png', $brand->getImage());
        $this->assertSame('Hyvor Blogs', $brand->getName());
    }

}