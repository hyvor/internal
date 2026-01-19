<?php

namespace Hyvor\Internal\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Hyvor\Internal\Sudo\SudoAuditLogRepository;

#[ORM\Entity(repositoryClass: SudoAuditLogRepository::class)]
#[ORM\Table(name: "sudo_audit_logs")]
class SudoAuditLog
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "bigint")]
    private int $user_id;

    #[ORM\Column(type: "text")]
    private string $action;

    /** @var array<string,scalar> $payload */
    #[ORM\Column(type: "json", options: ["jsonb" => true])]
    private array $payload;

    #[ORM\Column(type: "datetime_immutable")]
    private \DateTimeImmutable $created_at;

    #[ORM\Column(type: "datetime_immutable")]
    private \DateTimeImmutable $updated_at;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): static
    {
        $this->user_id = $user_id;
        return $this;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): static
    {
        $this->action = $action;
        return $this;
    }

    /** @return array<string,scalar> */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /** @param array<string,scalar> $payload */
    public function setPayload(array $payload): static
    {
        $this->payload = $payload;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;
        return $this;
    }

}
