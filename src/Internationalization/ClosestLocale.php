<?php

namespace Hyvor\Internal\Internationalization;

class ClosestLocale
{

    public function __construct(private readonly I18n $i18n)
    {
    }

    public function defLocale(): string
    {
        return $this->i18n->defaultLocale;
    }

    public static function get(?string $locale): string
    {
        $i18n = app(I18n::class);
        $locale ??= $i18n->defaultLocale;
        $locales = $i18n->getAvailableLocales();

        if (in_array($locale, $locales)) {
            return $locale;
        }

        $languagePart = explode('-', $locale)[0];

        foreach ($locales as $availableLocale) {
            if (explode('-', $availableLocale)[0] === $languagePart) {
                return $availableLocale;
            }
        }

        return $i18n->defaultLocale;
    }

}