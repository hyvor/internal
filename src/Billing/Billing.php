<?php

namespace Hyvor\Internal\Billing;

use Hyvor\Internal\Billing\Dto\LicenseOf;
use Hyvor\Internal\Billing\Dto\LicensesCollection;
use Hyvor\Internal\Billing\Exception\LicenseOfCombinationNotFoundException;
use Hyvor\Internal\Billing\License\License;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\Component\InstanceUrlResolver;
use Hyvor\Internal\InternalApi\Exceptions\InternalApiCallFailedException;
use Hyvor\Internal\InternalApi\InternalApi;
use Hyvor\Internal\InternalConfig;
use Hyvor\Internal\Util\Crypt\Encryption;

class Billing
{

    public function __construct(
        private InternalConfig $internalConfig,
        private InstanceUrlResolver $instanceUrlResolver,
        private InternalApi $internalApi,
        private Encryption $encryption
    ) {
    }

    /**
     * @return array{token: string, urlNew: string, urlChange: string}
     * @see SubscriptionIntent
     */
    public function subscriptionIntent(
        int $userId,
        string $planName,
        bool $isAnnual,
        ?Component $component = null,
    ): array {
        $component ??= $this->internalConfig->getComponent();

        // this validates the plan name as well
        $plan = $component->plans()->getPlan($planName);

        $object = new SubscriptionIntent(
            $component,
            $plan->version,
            $planName,
            $userId,
            $plan->monthlyPrice,
            $isAnnual,
        );

        $token = $this->encryption->encrypt($object);

        $baseUrl = $this->instanceUrlResolver->publicUrlOfCore() . '/account/billing/subscription?token=' . $token;

        return [
            'token' => $token,
            'urlNew' => $baseUrl,
            'urlChange' => $baseUrl . '&change=1',
        ];
    }

    /**
     * Get the license of a user.
     * @throws InternalApiCallFailedException
     * @throws LicenseOfCombinationNotFoundException
     */
    public function license(
        int $userId,
        ?int $resourceId,
        ?Component $component = null,
    ): ?License {
        $licenses = $this->licenses([new LicenseOf($userId, $resourceId)], $component);
        return $licenses->of($userId, $resourceId);
    }

    /**
     * @param array<LicenseOf> $of
     * @throws InternalApiCallFailedException
     */
    public function licenses(array $of, ?Component $component = null): LicensesCollection
    {
        $component ??= $this->internalConfig->getComponent();

        /**
         * @var array{user_id: int, resource_id: ?int, license: ?string}[] $response
         */
        $response = $this->internalApi->call(
            Component::CORE,
            '/billing/licenses',
            [
                'of' => array_map(fn($ofOne) => $ofOne->toArray(), $of)
            ],
            $component
        );

        return new LicensesCollection($response, $component);
    }

}
