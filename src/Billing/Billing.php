<?php

namespace Hyvor\Internal\Billing;

use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Bundle\Comms\CommsInterface;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\License\GetLicenses;
use Hyvor\Internal\Bundle\Comms\Exception\CommsApiFailedException;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\Component\InstanceUrlResolver;
use Hyvor\Internal\InternalConfig;

class Billing implements BillingInterface
{

    public function __construct(
        private InternalConfig $internalConfig,
        private InstanceUrlResolver $instanceUrlResolver,
        private CommsInterface $comms,
    ) {
    }

    /**
     * @return array{urlNew: string, urlChange: string}
     * @see SubscriptionIntent
     */
    public function subscriptionIntent(
        int $organizationId,
        string $planName,
        bool $isAnnual,
        ?Component $component = null,
        ?float $customMonthlyPrice = null,
    ): array {
        $component ??= $this->internalConfig->getComponent();

        // this validates the plan name as well
        $plan = $component->plans()->getPlan($planName);

        $intent = new SubscriptionIntent(
            $component,
            $plan->version,
            $planName,
            $organizationId,
            $customMonthlyPrice ?? $plan->monthlyPrice,
            $isAnnual,
        );

        $intentSerializedHex = bin2hex(serialize($intent));
        $signature = $this->comms->signature($intentSerializedHex);
        $params = http_build_query([
            'intent' => urlencode($intentSerializedHex),
            'signature' => $signature,
        ]);

        $baseUrl = $this->instanceUrlResolver->publicUrlOfCore() .
            '/account/billing/subscription?' .
            $params;

        return [
            'urlNew' => $baseUrl,
            'urlChange' => $baseUrl . '&change=1',
        ];
    }

    /**
     * Get the license of a user.
     * @throws CommsApiFailedException
     */
    public function license(
        int $organizationId,
        ?Component $component = null,
    ): ResolvedLicense {
        $licenses = $this->licenses([$organizationId], $component);
        return $licenses[$organizationId];
    }

    /**
     * @param int[] $organizationIds
     * @return array<int, ResolvedLicense> orgId keyed licenses.
     *                                     core ensures that all sent IDs are included as keys
     * @throws CommsApiFailedException
     */
    public function licenses(array $organizationIds, ?Component $component = null): array
    {
        $component ??= $this->internalConfig->getComponent();

        $response = $this->comms->send(
            new GetLicenses(
                $organizationIds,
                $component
            ),
            Component::CORE
        );

        return $response->getLicenses();
    }

}
