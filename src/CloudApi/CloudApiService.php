<?php

namespace Hyvor\Internal\CloudApi;

use Firebase\JWT\JWT;
use Hyvor\Internal\CloudApi\Scope\ScopeBuilder;
use Hyvor\Internal\InternalConfig;

class CloudApiService
{
    public function __construct(private InternalConfig $internalConfig) {}

    public function createJwtToken(
        string $privateKey,
        int $orgId,
        ScopeBuilder $scopeBuilder
    ): string
    {
        $now = time();

        $payload = [
            'iss' => $this->internalConfig->getInstance(),      // issuer
            'iat' => $now,                                      // issued at
            'nbf' => $now,                                      // not valid before
            'exp' => $now + 3600,                               // expires in 1 hour
            'sub' => (string) $orgId,                           // subject (organization ID)
            'scope' => $scopeBuilder->getScopeString(),         // ex: talk.org.websites.create
        ];

        return JWT::encode($payload, $privateKey, 'RS256');
    }

}
