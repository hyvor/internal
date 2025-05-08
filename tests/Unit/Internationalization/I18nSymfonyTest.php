<?php

namespace Hyvor\Internal\Tests\Unit\Internationalization;

use Hyvor\Internal\InternalConfig;
use Hyvor\Internal\Internationalization\I18n;
use Hyvor\Internal\Tests\Helper\Symfony\InternalConfigTestHelper;
use Hyvor\Internal\Tests\SymfonyTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(I18n::class)]
class I18nSymfonyTest extends SymfonyTestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        InternalConfigTestHelper::setContainerWithUpdatedProperty(
            $this->container,
            'i18nFolder',
            __DIR__ . '/locales'
        );
        // config(['internal.i18n.folder' => __DIR__ . '/locales']);
    }

    public function testI18nWorks(): void
    {
        $i18n = $this->container->get(I18n::class);
        $this->assertInstanceOf(I18n::class, $i18n);
        $this->assertEquals(['en-US', 'es', 'fr-FR'], $i18n->getAvailableLocales());
        $this->assertEquals('HYVOR', $i18n->getLocaleStrings('en-US')['name']);
        $this->assertCount(4, $i18n->getDefaultLocaleStrings());
    }

    public function testWhenFolderIsMissing(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not read the locales folder');

        config(['internal.i18n.folder' => '/missing-folder']);
        $i18n = app(I18n::class);
    }

    public function testThrowsOnCantRead(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Could not read the locale file of es');

        $i18n = app(I18n::class);
        $i18n->getLocaleStrings('es');
    }

    public function testWhenLocaleNotFound(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Locale pb not found');

        $i18n = app(I18n::class);
        $i18n->getLocaleStrings('pb');
    }

}