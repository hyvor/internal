<?php

namespace Hyvor\Internal\Internationalization;

class ClosestLocale
{

    public function __construct(private I18n $i18n)
    {
    }

    public function get(?string $locale): string
    {
        $locale ??= $this->i18n->defaultLocale;
        $locales = $this->i18n->getAvailableLocales();

        if (in_array($locale, $locales)) {
            return $locale;
        }

        $languagePart = explode('-', $locale)[0];

        foreach ($locales as $availableLocale) {
            if (explode('-', $availableLocale)[0] === $languagePart) {
                return $availableLocale;
            }
        }

        return $this->i18n->defaultLocale;
    }

}