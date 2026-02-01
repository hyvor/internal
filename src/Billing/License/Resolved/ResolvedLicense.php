<?php

namespace Hyvor\Internal\Billing\License\Resolved;

use Hyvor\Internal\Billing\License\License;

class ResolvedLicense implements \JsonSerializable
{

    public function __construct(
        public ResolvedLicenseType $type,
        public ?License $license = null,
        public ?ResolvedLicenseSubscription $subscription = null,
        public ?\DateTimeImmutable $trialEndsAt = null
    ) {
    }

    public function jsonSerialize(): mixed
    {
        return [
            'type' => $this->type->value,
            'license' => $this->license,
            'subscription' => $this->subscription,
            'trial_ends_at' => $this->trialEndsAt?->getTimestamp(),
        ];
    }
}
