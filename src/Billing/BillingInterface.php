<?php

namespace Hyvor\Internal\Billing;

use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Bundle\Comms\Exception\CommsApiFailedException;
use Hyvor\Internal\Component\Component;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

interface BillingInterface
{
    /**
     * @throws CommsApiFailedException
     */
    public function license(int $organizationId, ?Component $component = null): ResolvedLicense;

    /**
     * @param int[] $organizationIds
     * @return array<int, ResolvedLicense> orgId keyed licenses
     * @throws CommsApiFailedException
     */
    public function licenses(array $organizationIds, ?Component $component = null): array;

    /**
     * @param int $amount Total usage of the day
     * @param bool $force Overwrite the amount if the day is already recorded (no effect once invoiced)
     * @param bool $async Send via the messenger bus instead of a direct request to core
     * @param string $transport Messenger transport used when $async is true
     * @throws CommsApiFailedException when sent synchronously
     * @throws ExceptionInterface when dispatching async fails
     */
    public function recordMeteredUsage(
        int $organizationId,
        int $amount,
        \DateTimeImmutable $usageDate,
        bool $force = false,
        bool $async = false,
        string $transport = 'async',
    ): void;
}
