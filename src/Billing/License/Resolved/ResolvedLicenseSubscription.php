<?php

namespace Hyvor\Internal\Billing\License\Resolved;

class ResolvedLicenseSubscription
{

    public function __construct(
        public string $status,
        public float $monthlyPrice,
        public bool $isAnnual,
        public string $plan,
        public int $planVersion,
        public string $planReadableName,
        public ?\DateTimeImmutable $cancelAt
    ) {
    }

}