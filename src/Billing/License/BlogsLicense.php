<?php

namespace Hyvor\Internal\Billing\License;

class BlogsLicense extends License
{

    public function __construct(

        /**
         * Number of blog users (team members) allowed.
         */
        public int $users = 2,

        /**
         * Storage in bytes. Used when storing media files.
         */
        public int $storage = 1_000_000_000,

        /**
         * GPT Tokens per month.
         */
        public int $aiTokens = 1000,

        /**
         * DeepL characters per month in thousands.
         */
        public int $autoTranslationsChars = 1000,

        /**
         * Link and SEO analyses.
         */
        public bool $analyses = true, // SEO and link analysis
    )
    {
    }

}
