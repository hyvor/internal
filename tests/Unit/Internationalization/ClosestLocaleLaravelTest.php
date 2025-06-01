<?php

namespace Hyvor\Internal\Tests\Unit\Internationalization;

use Hyvor\Internal\Internationalization\ClosestLocale;
use Hyvor\Internal\Internationalization\I18n;
use Hyvor\Internal\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ClosestLocale::class)]
class ClosestLocaleLaravelTest extends LaravelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['internal.i18n.folder' => __DIR__ . '/locales']);
    }

    public function testGetsTheClosestLocale(): void
    {
        $closestLocale = app(ClosestLocale::class);

        $this->assertEquals('en-US', $closestLocale->get('en-US'));
        $this->assertEquals('en-US', $closestLocale->get('en-GB'));
        $this->assertEquals('fr-FR', $closestLocale->get('fr-FR'));
        $this->assertEquals('fr-FR', $closestLocale->get('fr'));
        $this->assertEquals('es', $closestLocale->get('es-ES'));
        $this->assertEquals('es', $closestLocale->get('es-MX'));
        $this->assertEquals('en-US', $closestLocale->get('pt'));
        $this->assertEquals('en-US', $closestLocale->get('invalid'));
    }
}
