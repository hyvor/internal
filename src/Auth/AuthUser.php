<?php

declare(strict_types=1);

namespace Hyvor\Internal\Auth;

use Hyvor\Internal\Bundle\Entity\OidcUser;

/**
 * @phpstan-type AuthUserArray array{
 *  id: int,
 *  username: string,
 *  name: string,
 *  email: string,
 *  picture_url?: string,
 *  location?: string,
 *  bio?: string,
 *  website_url?: string,
 * }
 *
 * @phpstan-type AuthUserArrayPartial array{
 * id?: int,
 * username?: string,
 * name?: string,
 * email?: string,
 * picture_url?: string,
 * location?: string,
 * bio?: string,
 * website_url?: string,
 * }
 */
class AuthUser
{

    final public function __construct(
        public int $id,
        public string $username,
        public string $name,
        public string $email,
        public ?string $picture_url = null,
        public ?string $location = null,
        public ?string $bio = null,
        public ?string $website_url = null,
        public ?string $oidc_sub = null,
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
            picture_url: $data['picture_url'] ?? null,
            location: $data['location'] ?? null,
            bio: $data['bio'] ?? null,
            website_url: $data['website_url'] ?? null,
        );
    }

    public static function fromOidcUser(OidcUser $oidcUser): static
    {
        return new static(
            id: $oidcUser->getId(),
            username: $oidcUser->getSub(),
            name: $oidcUser->getName(),
            email: $oidcUser->getEmail(),
            picture_url: $oidcUser->getPictureUrl(),
            location: null,
            bio: null,
            website_url: $oidcUser->getWebsiteUrl(),
            oidc_sub: $oidcUser->getSub(),
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
            'picture_url' => $this->picture_url,
            'location' => $this->location,
            'bio' => $this->bio,
            'website_url' => $this->website_url,
        ];

        return $user;
    }
}
