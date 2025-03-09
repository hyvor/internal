<?php

declare(strict_types=1);

namespace Hyvor\Internal\Auth;

use Hyvor\Internal\Bundle\Security\UserRole;
use Symfony\Component\Security\Core\User\UserInterface;

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
class AuthUser implements UserInterface
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

    /**
     * @return AuthUserArray
     */
    public function toArray(): array
    {
        /** @var AuthUserArray $user */
        $user = [
            'id' => $this->id,
            'username' => $this->username,
            'name' => $this->name,
            'email' => $this->email,
            'email_relay' => $this->email_relay,
            'picture_url' => $this->picture_url,
            'location' => $this->location,
            'bio' => $this->bio,
            'website_url' => $this->website_url,
        ];

        return $user;
    }

    public function getRoles(): array
    {
        return [UserRole::USER];
    }

    public function eraseCredentials(): void
    {
        return;
    }

    /**
     * Symfony requires a unique identifier for each user.
     * For user, both username and email are unique.
     * We use the username here.
     * However, since we do not use traditional symfony authentication, it does not matter much.
     */
    public function getUserIdentifier(): string
    {
        /** @var non-empty-string $username */
        $username = $this->username;
        return $username;
    }
}
