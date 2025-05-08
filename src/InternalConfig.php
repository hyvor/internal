<?php

namespace Hyvor\Internal;

use Hyvor\Internal\Component\Component;

readonly class InternalConfig
{

    private string $i18nDefaultLocale;

    public function __construct(

        /**
         * This is APP_KEY in laravel, which is base64:<key>
         * and APP_SECRET in symfony which is <key> in base64
         */
        private string $appSecret,

        /**
         * Component name
         */
        private string $component,
        private string $instance,
        private ?string $privateInstance,
        private bool $fake,

        /**
         * I18N
         */
        private string $i18nFolder,
        ?string $i18nDefaultLocale,
    ) {
        $this->i18nDefaultLocale = $i18nDefaultLocale ?? 'en-US';
    }

    public function getAppSecretRaw(): string
    {
        return $this->appSecret;
    }

    public function getAppSecret(): string
    {
        return base64_decode($this->appSecret);
    }

    public function getComponent(): Component
    {
        return Component::from($this->component);
    }

    public function getInstance(): string
    {
        return $this->instance;
    }

    public function getPrivateInstance(): ?string
    {
        return $this->privateInstance;
    }

    public function getPrivateInstanceWithFallback(): string
    {
        return $this->privateInstance ?? $this->instance;
    }

    public function isFake(): bool
    {
        return $this->fake;
    }

    public function getI18nFolder(): string
    {
        return $this->i18nFolder;
    }

    public function getI18nDefaultLocale(): string
    {
        return $this->i18nDefaultLocale;
    }

}