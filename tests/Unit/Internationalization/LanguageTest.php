<?php

namespace Hyvor\Internal\Tests\Unit\Internationalization;

use Hyvor\Internal\Internationalization\Language;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Language::class)]
class LanguageTest extends TestCase
{

    /**
     * @return array<string, array{string, ?Language}>
     */
    public static function codes(): array
    {
        return [
            'canonical en' => ['en', Language::EN],
            'canonical fr' => ['fr', Language::FR],

            // region-qualified: what products that register en-US/fr-FR send,
            // and what navigator.language gives
            'en-US' => ['en-US', Language::EN],
            'fr-FR' => ['fr-FR', Language::FR],
            'fr-CA' => ['fr-CA', Language::FR],
            'en-GB' => ['en-GB', Language::EN],

            'uppercase' => ['FR', Language::FR],

            'unsupported language' => ['de', null],
            'unsupported region' => ['de-DE', null],
            'empty' => ['', null],
            'garbage' => ['not a locale', null],
        ];
    }

    #[DataProvider('codes')]
    public function testClosest(string $code, ?Language $expected): void
    {
        $this->assertSame($expected, Language::closest($code));
    }

}
