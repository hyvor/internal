<?php

namespace Hyvor\Internal\Tests\Unit\Internationalization;

use Hyvor\Internal\Internationalization\Exceptions\FormatException;
use Hyvor\Internal\Internationalization\Exceptions\InvalidStringKeyException;
use Hyvor\Internal\Internationalization\Strings;
use Hyvor\Internal\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Strings::class)]
class StringsLaravelTest extends LaravelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['internal.i18n.folder' => __DIR__ . '/locales']);
    }

    public function testGetsStringsDefault(): void
    {
        $locale = new Strings('en-US');

        $this->assertEquals('HYVOR', $locale->get('name'));
        $this->assertEquals('Hello, you!', $locale->get('greet', ['name' => 'you']));
        $this->assertEquals('Sign up now', $locale->get('signup.cta'));

        // closest locale
        $locale = new Strings('en');
        $this->assertEquals('HYVOR', $locale->get('name'));
    }

    public function testGetsStringsNonDefault(): void
    {
        $locale = new Strings('fr-FR');
        $this->assertEquals('Bonjour, you!', $locale->get('greet', ['name' => 'you']));

        // fallback
        $this->assertEquals('HYVOR', $locale->get('name'));
    }

    public function testMissingLocale(): void
    {
        $locale = new Strings('si');
        $this->assertEquals('HYVOR', $locale->get('name'));
    }

    public function testThrowsOnInvalidKey(): void
    {
        $this->expectException(InvalidStringKeyException::class);
        (new Strings('en-US'))->get('invalid-key');
    }

    public function testWrongFormat(): void
    {
        $this->expectException(FormatException::class);
        (new Strings('en-US'))->get('badKey');
    }
}
