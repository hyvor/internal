<?php

namespace Hyvor\Internal;

use Hyvor\Internal\Component\Component;

class InternalConfig
{

    public function __construct(

        /**
         * This is APP_KEY in laravel, which is base64:<key>
         * and APP_SECRET in symfony which is <key> in base64
         */
        private string $appSecret,

        /**
         * COMMS_KEY env variable, which is used for encrypting communication in the Comms API
         * between the components
         * Previously, the app secret was used in the internal API, but to prevent all apps needing the same app secret,
         * the Comms API uses a separate key
         * Generated using: openssl rand -base64 32
         */
        private string $commsKey,

        /**
         * Component name
         */
        private string $component,

        /**
         * 'cloud' or 'on-prem'
         * env: DEPLOYMENT
         */
        private string $deployment,
        private string $instance,
        private ?string $privateInstance,
        private bool $fake,

        /**
         * I18N
         */
        private string $i18nFolder,
        private ?string $i18nDefaultLocale,
    ) {
    }

    public function getAppSecretRaw(): string
    {
        return $this->appSecret;
    }

    public function getAppSecret(): string
    {
        return base64_decode($this->appSecret);
    }

    public function getCommsKey(): string
    {
        return base64_decode($this->commsKey);
    }

    public function getComponent(): Component
    {
        return Component::from($this->component);
    }

    public function getDeployment(): Deployment
    {
        return Deployment::from($this->deployment);
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
        $realpath = realpath($this->i18nFolder);

        if ($realpath === false) {
            return $this->i18nFolder;
        }

        return $realpath;
    }

    public function getI18nDefaultLocale(): string
    {
        return $this->i18nDefaultLocale ?? 'en-US';
    }

}
