<?php

namespace Hyvor\Internal\Tests\Unit\Component;

use Hyvor\Internal\Component\Component;
use Hyvor\Internal\Component\InstanceUrlResolver;
use Hyvor\Internal\Tests\Helper\UpdatesInternalConfig;

trait InstanceUrlResolverTestTrait
{

    use UpdatesInternalConfig;

    abstract protected function getInstanceUrlResolver(): InstanceUrlResolver;

    public function test_public_url(): void
    {
        $resolver = $this->getInstanceUrlResolver();
        $this->assertEquals('https://hyvor.com', $resolver->publicUrlOf(Component::CORE));
        $this->assertEquals('https://hyvor.com', $resolver->publicUrlOfCore());
        $this->assertEquals('https://talk.hyvor.com', $resolver->publicUrlOf(Component::TALK));
    }

    public function test_public_url_two_levels_deep(): void
    {
        $this->updateInternalConfig('instance', 'https://hyvor.example.org');

        $resolver = $this->getInstanceUrlResolver();
        $this->assertEquals('https://hyvor.example.org', $resolver->publicUrlOf(Component::CORE));
        $this->assertEquals('https://talk.hyvor.example.org', $resolver->publicUrlOf(Component::TALK));
    }

    public function test_private_url(): void
    {
        $resolver = $this->getInstanceUrlResolver();
        $this->assertEquals('https://hyvor.internal', $resolver->privateUrlOf(Component::CORE));
        $this->assertEquals('https://hyvor.internal', $resolver->privateUrlOfCore());
        $this->assertEquals('https://talk.hyvor.internal', $resolver->privateUrlOf(Component::TALK));
    }

}