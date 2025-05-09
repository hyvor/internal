<?php

namespace Hyvor\Internal\Tests\Unit\Internationalization;

use Hyvor\Internal\Internationalization\Exceptions\FormatException;
use Hyvor\Internal\Internationalization\Exceptions\InvalidStringKeyException;
use Hyvor\Internal\Internationalization\Strings;
use Hyvor\Internal\Internationalization\StringsFactory;
use Hyvor\Internal\Tests\Helper\Symfony\InternalConfigTestHelper;
use Hyvor\Internal\Tests\SymfonyTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Strings::class)]
#[CoversClass(StringsFactory::class)]
class StringsSymfonyTest extends SymfonyTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        InternalConfigTestHelper::updateSymfony($this->container, 'i18nFolder', __DIR__ . '/locales');
    }

    private function getStrings(string $locale): Strings
    {
        $stringsFactory = $this->container->get(StringsFactory::class);
        assert($stringsFactory instanceof StringsFactory);
        return $stringsFactory->create($locale);
    }

    public function testGetsStringsDefault(): void
    {
        $strings = $this->getStrings('en-US');

        $this->assertEquals('HYVOR', $strings->get('name'));
        $this->assertEquals('Hello, you!', $strings->get('greet', ['name' => 'you']));
        $this->assertEquals('Sign up now', $strings->get('signup.cta'));

        // closest locale
        $strings = $this->getStrings('en');
        $this->assertEquals('HYVOR', $strings->get('name'));
    }

    public function testGetsStringsNonDefault(): void
    {
        $strings = $this->getStrings('fr-FR');
        $this->assertEquals('Bonjour, you!', $strings->get('greet', ['name' => 'you']));
        // fallback
        $this->assertEquals('HYVOR', $strings->get('name'));
    }

    public function testMissingLocale(): void
    {
        $locale = $this->getStrings('si');
        $this->assertEquals('HYVOR', $locale->get('name'));
    }

    public function testThrowsOnInvalidKey(): void
    {
        $this->expectException(InvalidStringKeyException::class);
        $this->getStrings('en-US')->get('invalid-key');
    }

    public function testWrongFormat(): void
    {
        $this->expectException(FormatException::class);
        $this->getStrings('en-US')->get('badKey');
    }

}