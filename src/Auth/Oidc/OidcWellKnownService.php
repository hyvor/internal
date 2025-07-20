<?php

namespace Hyvor\Internal\Auth\Oidc;

use Hyvor\Internal\Auth\Oidc\Exception\UnableToFetchWellKnownException;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;

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
    public function getWellKnownConfig(): OidcWellKnownConfig
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
    private function fetchWellKnownConfig(): OidcWellKnownConfig
    {
        $url = $this->config->getIssuerUrl() . '/.well-known/openid-configuration';

        try {
            $response = $this->httpClient->request('GET', $url);
            $data = $response->toArray();
        } catch (HttpExceptionInterface|TransportExceptionInterface|DecodingExceptionInterface $e) {
            $message = match (true) {
                $e instanceof HttpExceptionInterface => "HTTP error while fetching well-known config: {$e->getMessage()}",
                $e instanceof TransportExceptionInterface => "Transport error while fetching well-known config: {$e->getMessage()}",
                default => "Decoding error while fetching well-known config: {$e->getMessage()}",
            };
            throw new UnableToFetchWellKnownException($url, $message, previous: $e);
        }

        $checkedKeys = [
            'issuer',
            'authorization_endpoint',
            'token_endpoint',
            'userinfo_endpoint',
        ];
        foreach ($checkedKeys as $key) {
            if (!isset($data[$key])) {
                throw new UnableToFetchWellKnownException($url, "Missing key '$key' in discovery document.");
            }
            if (!is_string($data[$key])) {
                throw new UnableToFetchWellKnownException($url, "Key '$key' in discovery document must be a string.");
            }
        }

        return new OidcWellKnownConfig(
            issuer: $data['issuer'],
            authorizationEndpoint: $data['authorization_endpoint'],
            tokenEndpoint: $data['token_endpoint'],
            userinfoEndpoint: $data['userinfo_endpoint'],
        );
    }

}