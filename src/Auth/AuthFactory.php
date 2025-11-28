<?php

namespace Hyvor\Internal\Auth;

use Hyvor\Internal\Auth\Oidc\OidcAuth;
use Hyvor\Internal\InternalConfig;
use Hyvor\Internal\InternalFake;

class AuthFactory
{

    public function __construct(
        private InternalConfig $internalConfig,
        private Auth $hyvorAuth,
        private OidcAuth $oidcAuth,
    ) {
    }

    public function create(): AuthInterface
    {
        if ($this->internalConfig->getAuthMethod() === AuthMethod::HYVOR) {
            if ($this->internalConfig->isFake()) {
                $fake = InternalFake::getInstance();
                return new AuthFake($fake->user(), $fake->usersDatabase());
            }
            return $this->hyvorAuth;
        }

        return $this->oidcAuth;
    }

}