<?php

namespace Hyvor\Internal\Tests\Unit\Internationalization;

use Hyvor\Internal\Internationalization\ClosestLocale;
use Hyvor\Internal\Tests\LaravelTestCase;

class ClosestLocaleLaravelTest extends LaravelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['internal.i18n.folder' => __DIR__ . '/locales']);
    }

    public function testGetsTheClosestLocale(): void
    {
        $this->assertEquals('en-US', ClosestLocale::get('en-US'));
        $this->assertEquals('en-US', ClosestLocale::get('en-GB'));
        $this->assertEquals('fr-FR', ClosestLocale::get('fr-FR'));
        $this->assertEquals('fr-FR', ClosestLocale::get('fr'));
        $this->assertEquals('es', ClosestLocale::get('es-ES'));
        $this->assertEquals('es', ClosestLocale::get('es-MX'));
        $this->assertEquals('en-US', ClosestLocale::get('pt'));
        $this->assertEquals('en-US', ClosestLocale::get('invalid'));
    }
}
