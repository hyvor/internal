<?php

namespace Hyvor\Internal\Auth\Oidc\Dto;

use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
class OidcWellKnownConfigDto
{

    public function __construct(
        public string $issuer,
        public string $authorizationEndpoint,
        public string $tokenEndpoint,
        public string $userinfoEndpoint,
        public string $jwksUri,
    ) {
    }


}