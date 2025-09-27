<?php

namespace Hyvor\Internal\Billing\License;

class PostLicense extends License
{

    public function __construct(

        /**
         * Number of emails per month.
         * No emails allowed in the trial, need to upgrade to send the real emails (test emails can be sent)
         */
        public int $emails = 0,

        /**
         * Whether Hyvor Post branding is allowed to be removed.
         */
        public bool $allowRemoveBranding = false,

    ) {
    }

}
