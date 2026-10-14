<?php

namespace Hyvor\Internal\Internationalization;

/**
 * The languages supported across HYVOR products.
 *
 * Values are the canonical locale codes, without region parts, matching the
 * names of the JSON files in the locales folder.
 */
enum Language: string
{

    case EN = 'en';
    case FR = 'fr';

    /**
     * Resolves a locale code to the closest supported language, so clients can
     * send a region-qualified code (ex: fr-FR from a product that registers it
     * that way, or fr-CA from navigator.language) and still get a match.
     *
     * Returns null when nothing matches, so callers can reject rather than
     * silently fall back to the default language.
     */
    public static function closest(string $code): ?self
    {
        $exact = self::tryFrom($code);

        if ($exact !== null) {
            return $exact;
        }

        $languagePart = strtolower(explode('-', $code)[0]);

        foreach (self::cases() as $case) {
            if ($case->value === $languagePart) {
                return $case;
            }
        }

        return null;
    }

}
