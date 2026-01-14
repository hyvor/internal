<?php

namespace Hyvor\Internal\Auth\Oidc;

use Hyvor\Internal\Auth\Oidc\Dto\OidcWellKnownConfigDto;

class JwkHelper
{

    public static function getDefaultAlg(OidcWellKnownConfigDto $wellKnown): ?string
    {
        /**
         * Microsoft's JWKS does not include alg
         * https://github.com/hyvor/internal/issues/71
         */
        if (str_starts_with($wellKnown->issuer, 'https://login.microsoftonline.com/')) {
            return 'RS256';
        }

        return null; // Firebase uses the alg from the header
    }

}