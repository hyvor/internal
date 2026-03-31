<?php 

namespace Hyvor\Internal\Auth\Dto;

use Hyvor\Internal\Auth\AuthUser;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

/**
 * @phpstan-import-type AuthUserArray from AuthUser
 *
 * @phpstan-type OrganizationArray array{
 *     id: int,
 *     name: string,
 *     members_count: int,
 *     created_user?: AuthUserArray|AuthUser,
 *     billing_email?: string,
 *     billing_address?: BillingAddress|null,
 * }
 *
 * @phpstan-type OrganizationArrayPartial array{
 *     id?: int,
 *     name?: string,
 *     members_count?: int,
 *     created_user?: AuthUserArray,
 *     billing_email?: string,
 *     billing_address?: BillingAddress|null,
 * }
 *
 * @phpstan-type BillingAddress array{
 *     line1: string,
 *     city: string,
 *     state: string,
 *     postal_code: string,
 *     country: string,
 * }
 */
#[Exclude]
final class Organization {
    private AuthUser $created_user;
    private string $billing_email;

    /**
     * @var BillingAddress|null
     */
    private ?array $billing_address;

    public function __construct(
        private int $id,
        private string $name,
        private int $members_count,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMembersCount(): int
    {
        return $this->members_count;
    }

    public function getCreatedUser(): AuthUser
    {
        return $this->created_user;
    }

    public function setCreatedUser(AuthUser $created_user): void
    {
        $this->created_user = $created_user;
    }

    public function getBillingEmail(): string
    {
        return $this->billing_email;
    }

    public function setBillingEmail(string $billing_email): void
    {
        $this->billing_email = $billing_email;
    }

    /**
     * @return BillingAddress|null
     */
    public function getBillingAddress(): ?array
    {
        return $this->billing_address;
    }

    /**
     * @param BillingAddress|null $billing_address
     */
    public function setBillingAddress(?array $billing_address): void
    {
        $this->billing_address = $billing_address;
    }

    /**
     * @param OrganizationArray $data
     */
    public static function fromArray(array $data): self
    {
        $org = new self(
            id: $data['id'],
            name: $data['name'],
            members_count: $data['members_count'],
        );

        if (isset($data['created_user'])) {
            $org->setCreatedUser(
                $data['created_user'] instanceof AuthUser ?
                    $data['created_user'] :
                    AuthUser::fromArray($data['created_user'])
            );
        }

        if (isset($data['billing_email'])) {
            $org->setBillingEmail($data['billing_email']);
        }

        if (isset($data['billing_address'])) {
            $org->setBillingAddress($data['billing_address']);
        }

        return $org;
    }
}
