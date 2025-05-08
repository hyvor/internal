<?php

namespace Hyvor\Internal\Internationalization;

use Hyvor\Internal\Internationalization\Exceptions\FormatException;
use Hyvor\Internal\Internationalization\Exceptions\InvalidStringKeyException;
use MessageFormatter;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
class Strings
{

    public function __construct(
        private I18n $i18n,
        // already got the closest locale
        private string $locale,
    ) {
    }

    /**
     * @param array<mixed> $params
     * @throws InvalidStringKeyException
     * @throws FormatException
     */
    public function get(string $key, array $params = []): string
    {
        $currentLocaleStrings = $this->i18n->getLocaleStrings($this->locale);

        $string = $this->getFromDotNotation($currentLocaleStrings, $key);

        if ($string === null) {
            $defaultLocaleStrings = $this->i18n->getDefaultLocaleStrings();
            $string = $this->getFromDotNotation($defaultLocaleStrings, $key);

            if ($string === null) {
                throw new InvalidStringKeyException('Invalid string key: ' . $key);
            }
        }

        $formatted = MessageFormatter::formatMessage($this->locale, $string, $params);

        if (!$formatted) {
            throw new FormatException('Unable to format message: ' . $string);
        }

        return $formatted;
    }

    /**
     * @param array<mixed> $arr
     */
    private function getFromDotNotation(array $arr, string $key): ?string
    {
        $keys = explode('.', $key);
        $len = count($keys);

        foreach ($keys as $index => $key) {
            if ($index === $len - 1) {
                break; // don't processs the last element
            } else {
                if (!isset($arr[$key]) || !is_array($arr[$key])) {
                    return null; // @codeCoverageIgnore
                }
                $arr = $arr[$key];
            }
        }

        $val = $arr[$key] ?? null;
        return is_string($val) ? $val : null;
    }

}
