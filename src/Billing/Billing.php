<?php

namespace Hyvor\Internal\Billing;

use Hyvor\Internal\Billing\Dto\LicensesCollection;
use Hyvor\Internal\Billing\Exception\LicenseOfCombinationNotFoundException;
use Hyvor\Internal\Billing\License\License;
use Hyvor\Internal\Billing\Dto\LicenseOf;
use Hyvor\Internal\InternalApi\ComponentType;
use Hyvor\Internal\InternalApi\Exceptions\InternalApiCallFailedException;
use Hyvor\Internal\InternalApi\InstanceUrl;
use Hyvor\Internal\InternalApi\InternalApi;
use Hyvor\Internal\InternalApi\InternalApiMethod;

class Billing
{

    /**
     * @return array{token: string, urlNew: string, urlChange: string}
     * @see SubscriptionIntent
     */
    public function subscriptionIntent(
        int $userId,
        string $planName,
        bool $isAnnual,
        ?ComponentType $component = null,
    ): array {
        $component ??= ComponentType::current();

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

        $token = $object->encrypt();

        $baseUrl = InstanceUrl::getInstanceUrl() . '/account/billing/subscription?token=' . $token;

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
        ?ComponentType $component = null,
    ): ?License {
        $licenses = $this->licenses([new LicenseOf($userId, $resourceId)], $component);
        return $licenses->of($userId, $resourceId);
    }

    /**
     * @param array<LicenseOf> $of
     * @throws InternalApiCallFailedException
     */
    public function licenses(array $of, ?ComponentType $component = null): LicensesCollection
    {
        $component ??= ComponentType::current();

        /**
         * @var array{user_id: int, resource_id: ?int, license: ?string}[] $response
         */
        $response = InternalApi::call(
            ComponentType::CORE,
            InternalApiMethod::GET,
            '/billing/licenses',
            [
                'of' => array_map(fn($ofOne) => $ofOne->toArray(), $of)
            ],
            $component
        );

        return new LicensesCollection($response, $component);
    }

}
