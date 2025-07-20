<?php

namespace Hyvor\Internal\Auth\Oidc;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class OidcConfig
{

    public function __construct(
        #[Autowire('%env(OIDC_ISSUER_URL)%')]
        private string $issuerUrl,

        #[Autowire('%env(OIDC_CLIENT_ID)%')]
        private string $clientId,

        #[Autowire('%env(OIDC_CLIENT_SECRET)%')]
        private string $clientSecret,
    ) {
    }

    public function getIssuerUrl(): string
    {
        return rtrim($this->issuerUrl, '/');
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

}