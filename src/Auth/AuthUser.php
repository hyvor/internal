<?php

declare(strict_types=1);

namespace Hyvor\Internal\Auth;

/**
 * @phpstan-type AuthUserArray array{
 *  id: int,
 *  username: string,
 *  name: string,
 *  email: string,
 *  email_relay?: string,
 *  picture_url?: string,
 *  location?: string,
 *  bio?: string,
 *  website_url?: string,
 *  sub?: string,
 * }
 *
 * @phpstan-type AuthUserArrayPartial array{
 * id?: int,
 * username?: string,
 * name?: string,
 * email?: string,
 * email_relay?: string,
 * picture_url?: string,
 * location?: string,
 * bio?: string,
 * website_url?: string,
 * sub?: string,
 * }
 */
class AuthUser
{

    final public function __construct(
        public int $id,
        public string $username,
        public string $name,
        public string $email,
        public ?string $email_relay = null,
        public ?string $picture_url = null,
        public ?string $location = null,
        public ?string $bio = null,
        public ?string $website_url = null,
    ) {
    }

    /**
     * @param AuthUserArray $data
     */
    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'],
            username: $data['username'],
            name: $data['name'],
            email: $data['email'],
            email_relay: $data['email_relay'] ?? null,
            picture_url: $data['picture_url'] ?? null,
            location: $data['location'] ?? null,
            bio: $data['bio'] ?? null,
            website_url: $data['website_url'] ?? null,
        );
    }

}
