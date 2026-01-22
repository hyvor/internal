<?php

namespace Hyvor\Internal\Billing\License\Resolved;

use Hyvor\Internal\Billing\License\License;

class ResolvedLicense
{

    public function __construct(
        public ResolvedLicenseType $type,
        public ?License $license = null,
        public ?ResolvedLicenseSubscription $subscription = null,
        public ?\DateTimeImmutable $trialEndsAt = null
    ) {
    }

}