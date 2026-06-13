<?php

namespace Hyvor\Internal\Billing\License;

use Hyvor\Internal\Billing\License\Property\LicenseProperty;

final class BlogsLicense extends License
{

    public function __construct(
        public int $users,
        public int $storage,
        public int $aiTokens,
        public int $autoTranslationsChars,
        public bool $seoAnalysis,
        public bool $linkAnalysis,
        public int $blogs, // 0 for unlimited
    ) {}

    public static function properties(): array
    {
        return [
            LicenseProperty::int('users')
                ->name('Users')
                ->description('Number of blog users (team members) allowed.'),

            LicenseProperty::int('storage')
                ->name('Storage')
                ->description('Maximum storage for uploaded media files in blogs')
                ->bytes(),

            LicenseProperty::int('aiTokens')
                ->name('AI Tokens')
                ->description('Number of AI tokens per month for content generation'),

            LicenseProperty::int('autoTranslationsChars')
                ->name('Auto Translation Characters')
                ->description('Number of characters for automatic translations per month'),

            LicenseProperty::bool('seoAnalysis')
                ->name('SEO Analysis')
                ->description('Enable in-post SEO analysis for blog posts'),

            LicenseProperty::bool('linkAnalysis')
                ->name('Link Analysis')
                ->description('Enable post link analysis and bi-weekly full-blog link checks'),

            LicenseProperty::int('blogs')
                ->name('Blogs')
                ->description('Number of blogs allowed under this license.')
                ->note('Set to 0 for unlimited'),
        ];
    }

    public static function trial(): static
    {
        return new self(
            users: 2,
            storage: 1_000_000_000, // 1GB
            aiTokens: 1_000,
            autoTranslationsChars: 1000,
            seoAnalysis: true,
            linkAnalysis: true,
            blogs: 0,
        );
    }

}
