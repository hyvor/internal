<?php

namespace Hyvor\Internal\Tests\Unit\InternalApi;

use Hyvor\Internal\Component\Component;
use Hyvor\Internal\Component\ComponentUrlResolver;
use Hyvor\Internal\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ComponentUrlResolver::class)]
class InstanceUrlTest extends TestCase
{

    public function testCreates(): void
    {
        $i1 = ComponentUrlResolver::create();
        $this->assertEquals('https://hyvor.com', $i1->url);

        $i2 = ComponentUrlResolver::create('https://example.com');
        $this->assertEquals('https://example.com', $i2->url);

        $i3 = ComponentUrlResolver::createPrivate();
        $this->assertEquals('https://hyvor.com', $i3->url);

        config(['internal.private_instance' => 'https://hyvor.cluster']);
        $i4 = ComponentUrlResolver::createPrivate();
        $this->assertEquals('https://hyvor.cluster', $i4->url);
    }

    public function testComponentUrl(): void
    {
        $instanceUrl = ComponentUrlResolver::create();
        $this->assertEquals('https://hyvor.com', $instanceUrl->componentUrl(Component::CORE));
        $this->assertEquals('https://talk.hyvor.com', $instanceUrl->componentUrl(Component::TALK));

        // two levels deep
        $instanceUrl = ComponentUrlResolver::create('https://hyvor.example.org');
        $this->assertEquals('https://hyvor.example.org', $instanceUrl->componentUrl(Component::CORE));
        $this->assertEquals('https://talk.hyvor.example.org', $instanceUrl->componentUrl(Component::TALK));
    }

}
