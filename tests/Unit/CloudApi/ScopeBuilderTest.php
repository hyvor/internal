<?php

namespace Unit\CloudApi;

use Hyvor\Internal\CloudApi\Scope\PostScope;
use Hyvor\Internal\CloudApi\Scope\ScopeBuilder;
use Hyvor\Internal\CloudApi\Scope\TalkScope;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\Tests\SymfonyTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ScopeBuilder::class)]
class ScopeBuilderTest extends SymfonyTestCase
{

    public function test_adds_scopes(): void
    {
        $scopeBuilder = new ScopeBuilder();
        $scopeBuilder->addScopes(Component::TALK, [TalkScope::WEBSITE_READ, TalkScope::WEBSITE_WRITE]);

        $scopes = $scopeBuilder->getScopes();
        $this->assertArrayHasKey('talk', $scopes);
        $this->assertCount(2, $scopes['talk']);
        $this->assertContains('website.read', $scopes['talk']);
        $this->assertContains('website.write', $scopes['talk']);

        $scopeBuilder->addScopes(Component::POST, [PostScope::NEWSLETTER_READ]);
        $this->assertSame(
            'talk:website.read talk:website.write post:newsletter.read',
            $scopeBuilder->getScopeString()
        );
    }

    public function test_from_scope_string(): void
    {
        $scopeString = 'talk:website.read talk:website.write post:newsletter.read';
        $scopeBuilder = ScopeBuilder::fromScopeString($scopeString);

        $scopes = $scopeBuilder->getScopes();
        $this->assertArrayHasKey('talk', $scopes);
        $this->assertCount(2, $scopes['talk']);
        $this->assertContains('website.read', $scopes['talk']);
        $this->assertContains('website.write', $scopes['talk']);

        $this->assertArrayHasKey('post', $scopes);
        $this->assertCount(1, $scopes['post']);
        $this->assertContains('newsletter.read', $scopes['post']);
    }

}
