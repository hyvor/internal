<?php

namespace Hyvor\Internal\Tests\Unit;

use Hyvor\Internal\Component\Component;
use Hyvor\Internal\InternalConfig;
use Hyvor\Internal\Tests\SymfonyTestCase;

class InternalConfigTest extends SymfonyTestCase
{

    public function test_internal_config(): void
    {
        $internalConfig = new InternalConfig(
            appSecret: 'c2VjcmV0',
            component: 'core',
            authMethod: 'hyvor',
            instance: 'https://hyvor.com',
            privateInstance: 'https://hyvor.internal',
            fake: false,
            i18nFolder: 'i18n',
            i18nDefaultLocale: 'en'
        );

        $this->assertSame('c2VjcmV0', $internalConfig->getAppSecretRaw());
        $this->assertSame('secret', $internalConfig->getAppSecret());
        $this->assertSame(Component::CORE, $internalConfig->getComponent());
        $this->assertSame('https://hyvor.com', $internalConfig->getInstance());
        $this->assertSame('https://hyvor.internal', $internalConfig->getPrivateInstance());
        $this->assertSame('https://hyvor.internal', $internalConfig->getPrivateInstanceWithFallback());
        $this->assertFalse($internalConfig->isFake());
        $this->assertSame('i18n', $internalConfig->getI18nFolder());
        $this->assertSame('en', $internalConfig->getI18nDefaultLocale());
    }

    public function test_internal_config_i18n_realpath(): void
    {
        $internalConfig = new InternalConfig(
            appSecret: 'c2VjcmV0',
            component: 'core',
            authMethod: 'hyvor',
            instance: 'https://hyvor.com',
            privateInstance: 'https://hyvor.internal',
            fake: false,
            i18nFolder: __DIR__ . '/Resource',
            i18nDefaultLocale: 'en'
        );
        $this->assertStringEndsWith('/tests/Unit/Resource', $internalConfig->getI18nFolder());
    }

}