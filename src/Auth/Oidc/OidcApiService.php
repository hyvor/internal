<?php

namespace Hyvor\Internal\Auth\Oidc;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Hyvor\Internal\Auth\Oidc\Dto\OidcDecodedIdTokenDto;
use Hyvor\Internal\Auth\Oidc\Dto\OidcWellKnownConfigDto;
use Hyvor\Internal\Auth\Oidc\Exception\OidcApiException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OidcApiService
{

    public function __construct(
        private OidcConfig $oidcConfig,
        private HttpClientInterface $httpClient,
        private CacheInterface $cache,
        private LoggerInterface $logger,
        private DenormalizerInterface $denormalizer,
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * @throws OidcApiException
     */
    public function getWellKnownConfig(): OidcWellKnownConfigDto
    {
        $cacheKey = 'oidc_discovery_document' . md5($this->oidcConfig->getIssuerUrl());
        return $this->cache->get($cacheKey, function (ItemInterface $item) {
            $item->expiresAfter(24 * 60 * 60); // Cache for 24 hours
            return $this->fetchWellKnownConfig();
        });
    }

    /**
     * @throws OidcApiException
     */
    public function getDecodedIdToken(string $code, string $redirectUri): OidcDecodedIdTokenDto
    {
        $idToken = $this->getIdToken($code, $redirectUri);
        $jwks = $this->getJwks();
        $wellKnown = $this->getWellKnownConfig();

        try {
            $keys = JWK::parseKeySet($jwks, JwkHelper::getDefaultAlg($wellKnown));
            $decoded = JWT::decode($idToken, $keys);
        } catch (\Exception $e) {
            throw new OidcApiException('Invalid JWKS: ' . $e->getMessage());
        }

        try {
            $decoded->raw_token = $idToken;
            /** @var OidcDecodedIdTokenDto $decodedIdToken */
            $decodedIdToken = $this->denormalizer->denormalize($decoded, OidcDecodedIdTokenDto::class);
        } catch (\Throwable $e) {
            throw new OidcApiException('unable to decode ID token: ' . $e->getMessage());
        }

        $errors = $this->validator->validate($decodedIdToken);
        if (count($errors) > 0) {
            throw new OidcApiException((string) $errors);
        }

        return $decodedIdToken;
    }

    /**
     * @throws OidcApiException
     */
    private function fetchWellKnownConfig(): OidcWellKnownConfigDto
    {
        $url = $this->oidcConfig->getIssuerUrl() . '/.well-known/openid-configuration';

        try {
            $response = $this->httpClient->request('GET', $url);
            $data = $response->toArray();
        } catch (ExceptionInterface $e) {
            throw new OidcApiException($e->getMessage(), previous: $e);
        }

        $checkedKeys = [
            'issuer',
            'authorization_endpoint',
            'token_endpoint',
            'jwks_uri',
        ];
        foreach ($checkedKeys as $key) {
            if (!isset($data[$key])) {
                throw new OidcApiException("Missing key '$key' in discovery document.");
            }
            if (!is_string($data[$key])) {
                throw new OidcApiException("Key '$key' in discovery document must be a string.");
            }
        }

        return new OidcWellKnownConfigDto(
            issuer: $data['issuer'],
            authorizationEndpoint: $data['authorization_endpoint'],
            tokenEndpoint: $data['token_endpoint'],
            jwksUri: $data['jwks_uri'],
            endSessionEndpoint: $data['end_session_endpoint'] ?? null,
        );
    }

    /**
     * ID Token is a JWT that contains information about the user.
     * @throws OidcApiException
     */
    private function getIdToken(string $code, string $redirectUri): string
    {
        $wellKnownConfig = $this->getWellKnownConfig();
        $tokenEndpoint = $wellKnownConfig->tokenEndpoint;

        try {
            $response = $this->httpClient->request('POST', $tokenEndpoint, [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => [
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => $redirectUri,
                    'client_id' => $this->oidcConfig->getClientId(),
                    'client_secret' => $this->oidcConfig->getClientSecret(),
                ],
            ]);
            $data = $response->toArray();
        } catch (HttpExceptionInterface|TransportExceptionInterface|DecodingExceptionInterface $e) {
            throw new OidcApiException($e->getMessage(), previous: $e);
        }

        if (empty($data['id_token']) || !is_string($data['id_token'])) {
            throw new OidcApiException('ID Token not found in the response');
        }

        return $data['id_token'];
    }

    /**
     * @return array<mixed>
     * @throws OidcApiException
     */
    private function getJwks(): array
    {
        $jwsUri = $this->getWellKnownConfig()->jwksUri;

        try {
            $response = $this->httpClient->request('GET', $jwsUri);
            return $response->toArray();
        } catch (ExceptionInterface $e) {
            throw new OidcApiException('Failed to fetch JWKS: ' . $e->getMessage(), previous: $e);
        }
    }

}
