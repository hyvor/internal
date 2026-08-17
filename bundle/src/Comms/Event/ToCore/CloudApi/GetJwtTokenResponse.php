<?php

namespace Hyvor\Internal\Bundle\Comms\Event\ToCore\CloudApi;

readonly class GetJwtTokenResponse {

    public function __construct(
        private string $token,
        private \DateTimeImmutable $expiresAt
    ) {}

    public function getToken(): string
    {
        return $this->token;
    }

    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

}
