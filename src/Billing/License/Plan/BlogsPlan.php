<?php

namespace Hyvor\Internal\Billing\License\Plan;

use Hyvor\Internal\Billing\License\BlogsLicense;

/**
 * @extends PlanAbstract<BlogsLicense>
 */
class BlogsPlan extends PlanAbstract
{

    const int GB = 10 ** 9;

    protected function config(): void
    {
        // Version 1
        $this->version(1, function () {
            $this->plan(
                'starter',
                9,
                new BlogsLicense(
                    users: 2,
                    storage: 1 * self::GB,
                    aiCost: 500, // $5.00
                    seoAnalysis: false,
                    linkAnalysis: false,
                    blogs: -1,
                    noBranding: false,
                )
            );

            $this->plan(
                'growth',
                19,
                new BlogsLicense(
                    users: 5,
                    storage: 40 * self::GB,
                    aiCost: 2000, // $20.00
                    seoAnalysis: true,
                    linkAnalysis: true,
                    blogs: -1,
                    noBranding: true,
                )
            );

            $this->plan(
                'premium',
                49,
                new BlogsLicense(
                    users: 15,
                    storage: 250 * self::GB,
                    aiCost: 6000, // $60.00
                    seoAnalysis: true,
                    linkAnalysis: true,
                    blogs: -1,
                    noBranding: true,
                )
            );
        });

        // Version 2: 2025-02
        $this->version(2, function () {
            $this->plan(
                'personal',
                5,
                new BlogsLicense(
                    users: 1,
                    storage: 1 * self::GB,
                    aiCost: 0,
                    seoAnalysis: false,
                    linkAnalysis: false,
                    blogs: 1,
                    noBranding: false,
                ),
                nameReadable: 'Personal',
                annualOnly: true,
            );

            $this->plan(
                'starter',
                12,
                new BlogsLicense(
                    users: 5,
                    storage: 5 * self::GB,
                    aiCost: 500, // $5.00
                    seoAnalysis: true,
                    linkAnalysis: false,
                    blogs: -1,
                    noBranding: true,
                )
            );

            $this->plan(
                'growth',
                40,
                new BlogsLicense(
                    users: 15,
                    storage: 150 * self::GB,
                    aiCost: 2000, // $20.00
                    seoAnalysis: true,
                    linkAnalysis: true,
                    blogs: -1,
                    noBranding: true,
                )
            );

            $this->plan(
                'premium',
                125,
                new BlogsLicense(
                    users: 50,
                    storage: 500 * self::GB,
                    aiCost: 6000, // $60.00
                    seoAnalysis: true,
                    linkAnalysis: true,
                    blogs: -1,
                    noBranding: true,
                )
            );
        });
    }
}
