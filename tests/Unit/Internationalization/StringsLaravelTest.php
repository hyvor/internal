<?php

namespace Hyvor\Internal\Tests\Unit\Internationalization;

use Hyvor\Internal\Internationalization\Exceptions\FormatException;
use Hyvor\Internal\Internationalization\Exceptions\InvalidStringKeyException;
use Hyvor\Internal\Internationalization\Strings;
use Hyvor\Internal\Internationalization\StringsFactory;
use Hyvor\Internal\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Strings::class)]
#[CoversClass(StringsFactory::class)]
class StringsLaravelTest extends LaravelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['internal.i18n.folder' => __DIR__ . '/locales']);
    }

    public function testGetsStringsDefault(): void
    {
        $stringsFactory = app(StringsFactory::class);
        $strings = $stringsFactory->create('en-US');

        $this->assertEquals('HYVOR', $strings->get('name'));
        $this->assertEquals('Hello, you!', $strings->get('greet', ['name' => 'you']));
        $this->assertEquals('Sign up now', $strings->get('signup.cta'));

        // closest locale
        $this->assertEquals('HYVOR', $strings->get('name'));
    }

    public function testGetsStringsNonDefault(): void
    {
        $stringsFactory = app(StringsFactory::class);
        $strings = $stringsFactory->create('fr-FR');

        $this->assertEquals('Bonjour, you!', $strings->get('greet', ['name' => 'you']));

        // fallback
        $this->assertEquals('HYVOR', $strings->get('name'));
    }

    public function testMissingLocale(): void
    {
        $strings = app(StringsFactory::class)->create('si');
        $this->assertEquals('HYVOR', $strings->get('name'));
    }

    public function testThrowsOnInvalidKey(): void
    {
        $this->expectException(InvalidStringKeyException::class);
        app(StringsFactory::class)->create('en-US')->get('invalid-key');
    }

    public function testWrongFormat(): void
    {
        $this->expectException(FormatException::class);
        app(StringsFactory::class)->create('en-US')->get('badKey');
    }
}
