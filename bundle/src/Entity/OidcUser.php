<?php

namespace Hyvor\Internal\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Hyvor\Internal\Auth\Oidc\Repository\OidcUserRepository;

#[ORM\Entity(repositoryClass: OidcUserRepository::class)]
#[ORM\Table(name: 'oidc_users')]
class OidcUser
{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue()]
    private int $id;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $created_at;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updated_at;

    #[ORM\Column()]
    private string $iss;

    #[ORM\Column()]
    private string $sub;

    #[ORM\Column()]
    private string $email;

    #[ORM\Column()]
    private string $name;

    #[ORM\Column()]
    private ?string $picture_url = null;

    #[ORM\Column()]
    private ?string $website_url = null;

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setId(int $id): static
    {
        $this->id = $id;
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

    public function getIss(): string
    {
        return $this->iss;
    }

    public function setIss(string $iss): static
    {
        $this->iss = $iss;
        return $this;
    }

    public function getSub(): string
    {
        return $this->sub;
    }

    public function setSub(string $sub): static
    {
        $this->sub = $sub;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getPictureUrl(): ?string
    {
        return $this->picture_url;
    }

    public function setPictureUrl(?string $picture_url): static
    {
        $this->picture_url = $picture_url;
        return $this;
    }

    public function getWebsiteUrl(): ?string
    {
        return $this->website_url;
    }

    public function setWebsiteUrl(?string $website_url): static
    {
        $this->website_url = $website_url;
        return $this;
    }

}