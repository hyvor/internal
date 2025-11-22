<?php

declare(strict_types=1);

namespace Hyvor\Internal\Auth;

use Hyvor\Internal\Bundle\Entity\OidcUser;

/**
 * @phpstan-type AuthUserArray array{
 *  current_organization?: array{id: int, name: string},
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
 * current_organization?: array{id: int, name: string},
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
        public ?AuthCurrentOrganization $current_organization, // only set in AuthUser objects from ->check()
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
            current_organization: isset($data['current_organization']) ? new AuthCurrentOrganization(
                id: $data['current_organization']['id'],
                name: $data['current_organization']['name'],
            ) : null,
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
            current_organization: new AuthCurrentOrganization(
                id: 0,
                name: 'Default',
            ),
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
            'current_organization' => $this->current_organization ? [
                'id' => $this->current_organization->id,
                'name' => $this->current_organization->name,
            ] : null,
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

    /**
     * Only call this to get a non-null organization from an AuthUser returned by ->check()
     */
    public function getCurrentOrganization(): AuthCurrentOrganization
    {
        $org = $this->current_organization;
        assert($org instanceof AuthCurrentOrganization);
        return $org;
    }
}
