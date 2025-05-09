<?php

namespace Hyvor\Internal\Tests\Unit\InternalApi;

use Hyvor\Internal\Component\Component;
use Hyvor\Internal\Component\InstanceUrlResolver;
use Hyvor\Internal\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(InstanceUrlResolver::class)]
class InstanceUrlLaravelTest extends LaravelTestCase
{

    public function testCreates(): void
    {
        $i1 = InstanceUrlResolver::create();
        $this->assertEquals('https://hyvor.com', $i1->url);

        $i2 = InstanceUrlResolver::create('https://example.com');
        $this->assertEquals('https://example.com', $i2->url);

        $i3 = InstanceUrlResolver::createPrivate();
        $this->assertEquals('https://hyvor.com', $i3->url);

        config(['internal.private_instance' => 'https://hyvor.cluster']);
        $i4 = InstanceUrlResolver::createPrivate();
        $this->assertEquals('https://hyvor.cluster', $i4->url);
    }

    public function testComponentUrl(): void
    {
        $instanceUrl = InstanceUrlResolver::create();
        $this->assertEquals('https://hyvor.com', $instanceUrl->componentUrl(Component::CORE));
        $this->assertEquals('https://talk.hyvor.com', $instanceUrl->componentUrl(Component::TALK));

        // two levels deep
        $instanceUrl = InstanceUrlResolver::create('https://hyvor.example.org');
        $this->assertEquals('https://hyvor.example.org', $instanceUrl->componentUrl(Component::CORE));
        $this->assertEquals('https://talk.hyvor.example.org', $instanceUrl->componentUrl(Component::TALK));
    }

}
