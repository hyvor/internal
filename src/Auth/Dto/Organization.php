<?php 

namespace Hyvor\Internal\Auth\Dto;

use Hyvor\Internal\Auth\AuthUser;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

/**
 * @phpstan-type OrganizationArray array{
 *     id: int,
 *     name: string,
 *     members_count: int,
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
class Organization {
    final public function __construct(
        public int $id,
        public string $name,
        public int $members_count,
    ) {
    }

    public AuthUser $created_user;

    public string $billing_email;

    /**
     * @var BillingAddress|null
     */
    public ?array $billing_address;

    /**
     * @param OrganizationArray $data
     */
    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'],
            name: $data['name'],
            members_count: $data['members_count'],
        );
    }
}