<?php

namespace Hyvor\Internal\Billing\License;

use Hyvor\Internal\Billing\License\Property\LicenseProperty;

final class TalkLicense extends License
{

    public function __construct(
        public int $credits,
        public int $comments,
        public int $storage, // 100MB
        public bool $sso,
        public bool $noBranding,
        public bool $webhooks,
        public int $websites, // 0 for unlimited
        public int $moderators, // 0 for unlimited
        public bool $rules,
    ) {}

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

            LicenseProperty::int('websites')
                ->name('Websites')
                ->description('Number of websites allowed under this license.')
                ->note('Set to 0 for unlimited'),

            LicenseProperty::int('moderators')
                ->name('Moderators')
                ->description('Number of moderators allowed under this license.')
                ->note('Set to 0 for unlimited'),

            LicenseProperty::bool('rules')
                ->name('Rules')
                ->description('Enable advanced moderation rules to automate your moderation process'),
        ];
    }

    public static function trial(): static
    {
        return new self(
            credits: 1_000,
            comments: -1,
            storage: 100_000_000,
            sso: true,
            noBranding: false,
            webhooks: true,
            websites: 0,
            moderators: 0,
            rules: true,
        );
    }
}
