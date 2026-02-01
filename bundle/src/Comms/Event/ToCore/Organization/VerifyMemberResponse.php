<?php

namespace Hyvor\Internal\Bundle\Comms\Event\ToCore\Organization;

readonly class VerifyMemberResponse {

    public function __construct(
        private bool $isMember,
        private ?string $role,
    ) {}

    public function isMember(): bool
    {
        return $this->isMember;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

}
