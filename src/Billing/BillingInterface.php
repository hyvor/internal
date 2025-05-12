<?php

namespace Hyvor\Internal\Billing;

use Hyvor\Internal\Billing\License\License;
use Hyvor\Internal\Component\Component;

interface BillingInterface
{
    public function license(int $userId, ?int $resourceId, ?Component $component = null): ?License;
}