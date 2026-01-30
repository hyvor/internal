<?php

namespace Hyvor\Internal\Auth;

class AuthUserOrganization
{

    public function __construct(
        public int $id,
        public string $name,
        /**
         * @var 'admin' | 'manager' | 'member' | 'billing'
         */
        public string $role,
    ) {
    }

}
