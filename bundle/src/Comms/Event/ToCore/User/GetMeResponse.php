<?php

namespace Hyvor\Internal\Bundle\Comms\Event\ToCore\User;

use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Auth\AuthUserOrganization;

readonly class GetMeResponse {

    public function __construct(
        private ?AuthUser $user = null,
        private ?AuthUserOrganization $organization = null,
    ) {}

    public function getUser(): ?AuthUser
    {
        return $this->user;
    }

    public function getOrganization(): ?AuthUserOrganization
    {
        return $this->organization;
    }

}
