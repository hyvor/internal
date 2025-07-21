<?php

namespace Hyvor\Internal\Auth\Oidc;

use Hyvor\Internal\Auth\Oidc\Exception\UnableToFetchIdTokenException;
use Hyvor\Internal\Auth\Oidc\Exception\UnableToFetchWellKnownException;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OidcTokenService
{

    public function __construct(
        private OidcConfig $oidcConfig,
        private OidcWellKnownService $oidcWellKnownService,
        private HttpClientInterface $httpClient,
    ) {
    }

    /**
     * ID Token is a JWT that contains information about the user.
     * @throws UnableToFetchIdTokenException
     * @throws UnableToFetchWellKnownException
     */
    public function getIdToken(string $code): string
    {
        $wellKnownConfig = $this->oidcWellKnownService->getWellKnownConfig();
        $tokenEndpoint = $wellKnownConfig->tokenEndpoint;

        try {
            $response = $this->httpClient->request('POST', $tokenEndpoint, [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => [
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => $this->oidcConfig->getCallbackUrl(),
                    'client_id' => $this->oidcConfig->getClientId(),
                    'client_secret' => $this->oidcConfig->getClientSecret(),
                ],
            ]);
            $data = $response->toArray();
        } catch (HttpExceptionInterface|TransportExceptionInterface|DecodingExceptionInterface $e) {
            throw new UnableToFetchIdTokenException($e->getMessage(), previous: $e);
        }

        if (empty($data['id_token']) || !is_string($data['id_token'])) {
            throw new UnableToFetchIdTokenException('ID Token not found in the response');
        }

        return $data['id_token'];
    }

}