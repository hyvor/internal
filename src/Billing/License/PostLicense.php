<?php

namespace Hyvor\Internal\Billing\License;

use Hyvor\Internal\Billing\License\Property\LicenseProperty;

final class PostLicense extends License
{

    public function __construct(
        public int $emails, // 0 = email sending not allowed
        public bool $allowRemoveBranding,

    ) {
    }

    public static function properties(): array
    {
        return [
            LicenseProperty::int('emails')
                ->name('Emails')
                ->description('Number of emails allowed to be sent per month'),

            LicenseProperty::bool('allowRemoveBranding')
                ->name('Disable Branding')
                ->description('Disable Hyvor Post branding on emails'),
        ];
    }

    public static function trial(): static
    {
        return new self(
            emails: 0,
            allowRemoveBranding: false
        );
    }

}
