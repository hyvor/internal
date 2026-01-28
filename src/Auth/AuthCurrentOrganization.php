<?php

namespace Hyvor\Internal\Auth;

class AuthCurrentOrganization
{

    public function __construct(
        public int $id,
        public string $name,
    ) {
    }

}