<?php 

namespace Hyvor\Internal\Auth\Dto;

use Hyvor\Internal\Auth\AuthUser;

/**
 * @phpstan-type BillingAddress array{
 *         line1: string,
 *         city: string,
 *         state: string,
 *         postal_code: string,
 *         country: string,
 *     }
 */
class Organization {
    public function __construct(
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
}