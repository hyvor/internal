<?php

namespace Hyvor\Internal\Billing\License\Resolved;

class ResolvedLicenseSubscription implements \JsonSerializable
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

    public function jsonSerialize(): mixed
    {
        return [
            'status' => $this->status,
            'monthly_price' => $this->monthlyPrice,
            'is_annual' => $this->isAnnual,
            'plan' => $this->plan,
            'plan_version' => $this->planVersion,
            'plan_readable_name' => $this->planReadableName,
            'cancel_at' => $this->cancelAt?->getTimestamp(),
        ];
    }
}
