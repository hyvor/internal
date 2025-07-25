<?php

namespace Hyvor\Internal\Auth\Oidc;

use Hyvor\Internal\Auth\Oidc\Dto\OidcWellKnownConfigDto;
use Hyvor\Internal\Auth\Oidc\Exception\UnableToFetchWellKnownException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OidcWellKnownService
{

    public function __construct(
        private OidcConfig $config,
        private HttpClientInterface $httpClient,
        private CacheInterface $cache,
    ) {
    }

    /**
     * @throws UnableToFetchWellKnownException
     */
    public function getWellKnownConfig(): OidcWellKnownConfigDto
    {
        $cacheKey = 'oidc_discovery_document' . md5($this->config->getIssuerUrl());
        return $this->cache->get($cacheKey, function (ItemInterface $item) {
            $item->expiresAfter(24 * 60 * 60); // Cache for 24 hours
            return $this->fetchWellKnownConfig();
        });
    }

    /**
     * @throws UnableToFetchWellKnownException
     */
    private function fetchWellKnownConfig(): OidcWellKnownConfigDto
    {
        $url = $this->config->getIssuerUrl() . '/.well-known/openid-configuration';

        try {
            $response = $this->httpClient->request('GET', $url);
            $data = $response->toArray();
        } catch (HttpExceptionInterface|TransportExceptionInterface|DecodingExceptionInterface $e) {
            throw new UnableToFetchWellKnownException($e->getMessage(), previous: $e);
        }

        $checkedKeys = [
            'issuer',
            'authorization_endpoint',
            'token_endpoint',
            'userinfo_endpoint',
        ];
        foreach ($checkedKeys as $key) {
            if (!isset($data[$key])) {
                throw new UnableToFetchWellKnownException("Missing key '$key' in discovery document.");
            }
            if (!is_string($data[$key])) {
                throw new UnableToFetchWellKnownException("Key '$key' in discovery document must be a string.");
            }
        }

        return new OidcWellKnownConfigDto(
            issuer: $data['issuer'],
            authorizationEndpoint: $data['authorization_endpoint'],
            tokenEndpoint: $data['token_endpoint'],
            userinfoEndpoint: $data['userinfo_endpoint'],
        );
    }

}