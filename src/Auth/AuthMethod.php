<?php

namespace Hyvor\Internal\Auth;

enum AuthMethod: string
{

    case HYVOR = 'hyvor';
    case OIDC = 'oidc';

    public function isOidc(): bool
    {
        return $this === self::OIDC;
    }

}