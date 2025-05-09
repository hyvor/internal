<?php

namespace Hyvor\Internal\Billing\License;

class TalkLicense extends License
{

    public function __construct(

        /**
         * Number of maximum credits per month
         */
        public int $credits = 1_000,

        /**
         * Storage size in bytes
         */
        public int $storage = 100_000_000, // 100MB

        /**
         * Single sign-on (SSO) enabled or not.
         */
        public bool $sso = true,

        /**
         * Disable Hyvor Talk Branding
         */
        public bool $noBranding = false,

        /**
         * Webhooks
         */
        public bool $webhooks = true,

    ) {
    }

}