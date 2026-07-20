<?php

namespace Hyvor\Internal\CloudApi;

use Hyvor\Internal\Bundle\Comms\CommsInterface;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\CloudApi\GetJwtToken;
use Hyvor\Internal\Bundle\Comms\Exception\CommsApiFailedException;
use Hyvor\Internal\CloudApi\Scope\ScopeInterface;
use Hyvor\Internal\Component\Component;
use Hyvor\Sdk\Auth\TokenProviderInterface;
use Hyvor\Sdk\Exceptions\AuthenticationException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

class InternalCloudApiTokenProvider implements TokenProviderInterface
{

    /**
     * @param int $orgId
     * @param array<ScopeInterface> $scopes
     */
    public function __construct(
        private int $orgId,
        private Component $component, // component the token is for
        private array $scopes,
        private CommsInterface $comms, // fetches the token via the comms service
        private CacheItemPoolInterface $cache,
        private LoggerInterface $logger
    ) {}

    public function getToken(): string
    {

        $scopesStrings = array_map(fn($scope) => (string) $scope->value, $this->scopes);
        sort($scopesStrings);
        $cacheKey = "cloud_api_token_{$this->component->value}_{$this->orgId}_" . md5(implode(',', $scopesStrings));

        dump($cacheKey);
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        try {
            $response = $this->comms->send(new GetJwtToken(
                organizationId: $this->orgId,
                component: $this->component,
                scopes: $scopesStrings
            ));
        } catch (CommsApiFailedException $e) {

            $this->logger->error('Failed to fetch token from comms service', [
                'exception' => $e,
                'organizationId' => $this->orgId,
                'component' => $this->component->value,
                'scopes' => $scopesStrings
            ]);

            throw new AuthenticationException('Failed to fetch token from comms service', previous: $e);
        }

        $token = $response->getToken();

        $cacheItem->set($token);
        // expire 1 minute before actual expiry to avoid using an expired token
        $cacheItem->expiresAt($response->getExpiresAt()->modify('-1 minute'));

        $this->cache->save($cacheItem);

        return $token;

    }

}
