<?php

namespace Hyvor\Internal\Tests\Unit\Internationalization;

use Hyvor\Internal\Internationalization\ClosestLocale;
use Hyvor\Internal\Tests\Helper\Symfony\InternalConfigTestHelper;
use Hyvor\Internal\Tests\SymfonyTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ClosestLocale::class)]
class ClosestLocaleSymfonyTest extends SymfonyTestCase
{

    public function testGetsTheClosestLocale(): void
    {
        InternalConfigTestHelper::updateSymfony($this->container, 'i18nFolder', __DIR__ . '/locales');

        $closestLocale = $this->container->get(ClosestLocale::class);
        assert($closestLocale instanceof ClosestLocale);

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