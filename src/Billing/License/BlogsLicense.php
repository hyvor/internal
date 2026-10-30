<?php

namespace Hyvor\Internal\Billing\License;

use Hyvor\Internal\Billing\License\Property\LicenseProperty;

final class BlogsLicense extends License
{

    public function __construct(
        public int $users,
        public int $storage,
        public int $aiCost, // allowed AI cost per month, in USD cents (100 = $1.00). 0 = not included
        public bool $seoAnalysis,
        public bool $linkAnalysis,
        public int $blogs, // -1 for unlimited
        public bool $noBranding,
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

            LicenseProperty::int('aiCost')
                ->name('AI Cost')
                ->description('Allowed AI usage cost per month, in USD cents'),

            LicenseProperty::bool('seoAnalysis')
                ->name('SEO Analysis')
                ->description('Enable in-post SEO analysis for blog posts'),

            LicenseProperty::bool('linkAnalysis')
                ->name('Link Analysis')
                ->description('Enable post link analysis and bi-weekly full-blog link checks'),

            LicenseProperty::int('blogs')
                ->name('Blogs')
                ->description('Number of blogs allowed under this license.')
                ->note('Set to -1 for unlimited'),

            LicenseProperty::bool('noBranding')
                ->name('Disable Branding')
                ->description('Disable Hyvor Blogs Branding on your blog'),
        ];
    }

    public static function trial(): static
    {
        return new self(
            users: 2,
            storage: 1_000_000_000, // 1GB
            aiCost: 500, // $5.00
            seoAnalysis: true,
            linkAnalysis: true,
            blogs: -1,
            noBranding: false,
        );
    }

}
