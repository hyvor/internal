<?php

namespace Hyvor\Internal\Billing\License;

use Hyvor\Internal\Billing\License\Property\LicenseProperty;

final class TalkLicense extends License
{

    public function __construct(
        public int $credits,
        public int $storage, // 100MB
        public bool $sso,
        public bool $noBranding,
        public bool $webhooks,
    ) {
    }

    public static function properties(): array
    {
        return [
            LicenseProperty::int('credits')
                ->name('Credits')
                ->description('Number of maximum credits per month')
                ->note('Set to -1 for unlimited (if Comments limit is set)'),

            LicenseProperty::int('comments')
                ->name('Comments')
                ->description('Number of maximum comments per month')
                ->note('Only for Enterprise Contracts'),

            LicenseProperty::int('storage')
                ->name('Storage')
                ->description('Maximum storage for uploaded media files')
                ->bytes(),

            LicenseProperty::bool('sso')
                ->name('Single Sign-on')
                ->description('Enable Single Sign-on (SSO) for your users'),

            LicenseProperty::bool('noBranding')
                ->name('Disable Branding')
                ->description('Disable Hyvor Talk Branding on your website and emails'),

            LicenseProperty::bool('webhooks')
                ->name('Webhooks')
                ->description('Enable Webhooks to integrate with other services'),
        ];
    }

    public static function trial(): static
    {
        return new self(
            credits: 1_000,
            storage: 100_000_000,
            sso: true,
            noBranding: false,
            webhooks: true
        );
    }

}
