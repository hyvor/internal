<?php

namespace Hyvor\Internal\Internationalization;

readonly class StringsFactory
{

    public function __construct(
        private ClosestLocale $closestLocale,
        private I18n $i18n,
    ) {
    }

    public function create(string $locale): Strings
    {
        return new Strings($this->i18n, $this->closestLocale->get($locale));
    }

}