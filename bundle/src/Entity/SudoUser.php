<?php

namespace Hyvor\Internal\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Hyvor\Internal\Sudo\SudoUserRepository;

#[ORM\Entity(repositoryClass: SudoUserRepository::class)]
#[ORM\Table(name: 'sudo_users')]
class SudoUser
{

    #[ORM\Id]
    #[ORM\Column(type: 'bigint', unique: true)]
    private int $user_id;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $created_at;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updated_at;


    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): static
    {
        $this->user_id = $user_id;
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