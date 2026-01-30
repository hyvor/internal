<?php

namespace Hyvor\Internal\Auth\Dto;

use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Auth\AuthUserOrganization;

class Me {

    public function __construct(
        private AuthUser $user,
        private ?AuthUserOrganization $organization
    ) {}

    public function getUser(): AuthUser
    {
        return $this->user;
    }

    public function getOrganization(): ?AuthUserOrganization
    {
        return $this->organization;
    }

}
