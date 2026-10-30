<?php

namespace Hyvor\Internal\Billing\License;

use Hyvor\Internal\Billing\License\Property\LicenseProperty;

final class RelayLicense extends License
{
    public function __construct(
        public int $emails,
        public int $projects,
        public int $domains,
        public bool $dedicatedIps,
    ) {}

    public static function properties(): array
    {
        return [
            LicenseProperty::int('emails')
                ->name('Emails')
                ->description('The number of emails allowed to be sent per month'),

            LicenseProperty::int('projects')
                ->name('Projects')
                ->description('The number of projects allowed to create'),

            LicenseProperty::int('domains')
                ->name('Domains')
                ->description('The number of domains allowed to send emails from'),

            LicenseProperty::bool('dedicatedIps')
                ->name('Dedicated IPs')
                ->description('Whether user is allowed to purchase dedicated IPs'),
        ];
    }

    public static function trial(): static
    {
        return new self(
            emails: 50,
            projects: 1,
            domains: 1,
            dedicatedIps: false,
        );
    }
}
