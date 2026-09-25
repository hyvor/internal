<?php

namespace Hyvor\Internal\Billing\License\Resolved;

use Hyvor\Internal\Billing\License\License;
use Hyvor\Internal\Component\Component;

class ResolvedComplimentaryLicense
{

    public function __construct(
        public Component $provider,
        public ResolvedComplimentaryLicenseType $type,
        public License $license
    ) {}

}
