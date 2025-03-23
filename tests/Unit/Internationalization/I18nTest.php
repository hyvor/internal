<?php

namespace Hyvor\Internal\Tests\Unit\Internationalization;

use Hyvor\Internal\Internationalization\I18n;
use Hyvor\Internal\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use RuntimeException;

#[CoversClass(I18n::class)]
class I18nTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['internal.i18n.folder' => __DIR__ . '/locales']);
    }

    public function testI18nWorks(): void
    {
        $i18n = app(I18n::class);
        $this->assertEquals(['en-US', 'es', 'fr-FR'], $i18n->getAvailableLocales());
        $this->assertEquals('HYVOR', $i18n->getLocaleStrings('en-US')['name']);
    }

    public function testWhenFolderIsMissing(): void
    {
        $this->expectException(RuntimeException::class);
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
