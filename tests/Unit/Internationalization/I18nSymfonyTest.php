<?php

namespace Hyvor\Internal\Tests\Unit\Internationalization;

use Hyvor\Internal\Internationalization\I18n;
use Hyvor\Internal\Tests\Helper\UpdatesInternalConfig;
use Hyvor\Internal\Tests\SymfonyTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(I18n::class)]
class I18nSymfonyTest extends SymfonyTestCase
{

    use UpdatesInternalConfig;

    protected function setUp(): void
    {
        parent::setUp();
        $this->updateInternalConfig('i18nFolder', __DIR__ . '/locales');
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

        $this->updateInternalConfig('i18nFolder', __DIR__ . '/missing-folder');
        $i18n = $this->container->get(I18n::class);
    }

    public function testThrowsOnCantRead(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not read the locale file of es');

        $i18n = $this->container->get(I18n::class);
        $this->assertInstanceOf(I18n::class, $i18n);
        $i18n->getLocaleStrings('es');
    }

    public function testWhenLocaleNotFound(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Locale pb not found');

        $i18n = $this->container->get(I18n::class);
        assert($i18n instanceof I18n);
        $i18n->getLocaleStrings('pb');
    }

}