<?php

namespace Hyvor\Internal\Auth\Oidc;

class OidcWellKnownConfig
{

    public function __construct(
        public string $issuer,
        public string $authorizationEndpoint,
        public string $tokenEndpoint,
        public string $userinfoEndpoint,
    ) {
    }


}