<?php

namespace Hyvor\Internal\CloudApi;

use Firebase\JWT\BeforeValidException;
use Firebase\JWT\CachedKeySet;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;
use Hyvor\Internal\Bundle\Comms\CommsInterface;
use Hyvor\Internal\CloudApi\Exception\JwtDecodeException;
use Hyvor\Internal\CloudApi\Scope\ScopeBuilder;
use Hyvor\Internal\CloudApi\Scope\ScopeInterface;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\InternalConfig;
use Hyvor\Sdk\HyvorClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CloudApiService
{

    private const string JWKS_URI = '/.well-known/jwks.json';

    public function __construct(
        private InternalConfig $internalConfig,
        private CacheItemPoolInterface $cache,
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private CommsInterface $comms
    ) {}

    public function createJwtToken(
        int $orgId,
        ScopeBuilder $scopeBuilder
    ): CloudJwt
    {
        return CloudJwt::create(
            $this->internalConfig->getInstance(),
            $orgId,
            $scopeBuilder
        );
    }

    /**
     * @throws JwtDecodeException
     */
    public function decodeJwtToken(string $jwtToken): CloudJwt
    {
        $jwksUri = $this->internalConfig->getInstance() . self::JWKS_URI;

        $psr17Factory = new Psr17Factory();

        /**
         * Symfony does not provide a way to convert its client to a PSR 17 request factory
         * So, Nyholm's PSR 7 is used.
         */
        $keySet = new CachedKeySet(
            $jwksUri,
            new Psr18Client($this->httpClient, $psr17Factory),
            $psr17Factory,
            $this->cache,
            null, // $expiresAfter int seconds to set the JWKS to expire
            true  // $rateLimit    true to enable rate limit of 10 RPS on lookup of invalid keys
        );

        try {
            $decoded = JWT::decode($jwtToken, $keySet);
        } catch (SignatureInvalidException|BeforeValidException|ExpiredException $e) {
            throw new JwtDecodeException('JWT decode error: ' . $e->getMessage(), previous: $e);
        } catch (\InvalidArgumentException|\DomainException|\UnexpectedValueException $e) {
            $this->logger->error('JWT decode failed: ' . $e->getMessage(), ['exception' => $e]);
            throw new JwtDecodeException('JWT decode failed due to internal error.', previous: $e);
        }

        try {
            $decodedArray = json_decode(json_encode($decoded, JSON_THROW_ON_ERROR), true, flags: JSON_THROW_ON_ERROR);
            return CloudJwt::fromArray($decodedArray);
        } catch (\JsonException $e) {
            throw new JwtDecodeException('JWT decode failed: ' . $e->getMessage(), previous: $e);
        }
    }


    /**
     * Gets a HyvorClient instance for the given organization ID and component
     * uses the given scopes.
     * Note: this fetches the JWT token via the comms API and caches it for 1 hour.
     *
     * @param array<ScopeInterface&\BackedEnum> $scopes
     */
    public function getHyvorClientForOrganization(
        int $orgId,
        Component $component,
        array $scopes,
    ): HyvorClient
    {

        return new HyvorClient(
            tokenProvider: new InternalCloudApiTokenProvider(
                orgId: $orgId,
                component: $component,
                scopes: $scopes,
                comms: $this->comms,
                cache: $this->cache,
                logger: $this->logger,
            ),
            cloudInstance: $this->internalConfig->getPrivateInstanceWithFallback(),
        );

    }


}
