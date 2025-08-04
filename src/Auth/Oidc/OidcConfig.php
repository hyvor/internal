<?php

namespace Hyvor\Internal\Auth\Oidc;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class OidcConfig
{

    public function __construct(
        #[Autowire('%env(OIDC_ISSUER_URL)%')]
        private string $issuerUrl,

        #[Autowire('%env(OIDC_CLIENT_ID)%')]
        private string $clientId,

        #[Autowire('%env(OIDC_CLIENT_SECRET)%')]
        private string $clientSecret,

        private ?RequestStack $requestStack = null,
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

    public function getCallbackUrl(?Request $request = null): string
    {
        $request ??= $this->requestStack?->getCurrentRequest();
        assert($request instanceof Request, 'This must be called in the context of a request.');
        $currentUrlOrigin = $request->getSchemeAndHttpHost();
        return $currentUrlOrigin . '/api/oidc/callback';
    }

}