<?php

namespace Hyvor\Internal\Auth\Dto;

use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Auth\AuthUserOrganization;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
class Me {

    public function __construct(
        private AuthUser $user,
        private ?AuthUserOrganization $organization,
        // session ID from HYVOR (used for audits mostly)
        private ?int $sessionId = null,
    ) {}

    public function getUser(): AuthUser
    {
        return $this->user;
    }

    public function getOrganization(): ?AuthUserOrganization
    {
        return $this->organization;
    }

    public function getSessionId(): ?int
    {
        return $this->sessionId;
    }

}
