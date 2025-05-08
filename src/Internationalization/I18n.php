<?php

namespace Hyvor\Internal\Internationalization;

use Hyvor\Internal\InternalConfig;
use RuntimeException;


class I18n
{

    public string $folder;

    /** @var string[] */
    public array $availableLocales;

    public string $defaultLocale;

    /** @var array<string, array<mixed>> */
    public array $stringsCache = [];

    public function __construct(InternalConfig $config)
    {
        $this->folder = $config->getI18nFolder();
        $this->availableLocales = $this->setAvailableLocales();
        $this->defaultLocale = $config->getI18nDefaultLocale();
        $this->stringsCache[$this->defaultLocale] = $this->getLocaleStrings($this->defaultLocale);
    }

    /**
     * @return array<string>
     */
    private function setAvailableLocales(): array
    {
        $locales = [];
        $files = @scandir($this->folder);

        if ($files === false) {
            throw new RuntimeException('Could not read the locales folder');
        }

        foreach ($files as $file) {
            if (is_file($this->folder . '/' . $file)) {
                $locales[] = pathinfo($file, PATHINFO_FILENAME);
            }
        }
        return $locales;
    }

    /**
     * @return string[]
     */
    public function getAvailableLocales(): array
    {
        return $this->availableLocales;
    }

    /**
     * @return array<mixed>
     */
    public function getLocaleStrings(string $locale): array
    {
        if (isset($this->stringsCache[$locale])) {
            return $this->stringsCache[$locale];
        }

        $file = $this->folder . '/' . $locale . '.json';

        if (!in_array($locale, $this->availableLocales)) {
            throw new RuntimeException("Locale $locale not found");
        }

        assert(file_exists($file));
        $json = file_get_contents($file);

        if (!$json) {
            throw new RuntimeException('Could not read the locale file of ' . $locale);
        }

        return (array)json_decode($json, true);
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefaultLocaleStrings(): array
    {
        return $this->stringsCache[$this->defaultLocale];
    }

}
