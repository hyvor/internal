<?php

namespace Hyvor\Internal\Auth\Oidc;

use Hyvor\Internal\Bundle\Entity\OidcUser;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<OidcUser>
 * @codeCoverageIgnore
 */
class OidcUserFactory extends PersistentProxyObjectFactory
{

    public function __construct()
    {
        parent::__construct();
    }

    public static function class(): string
    {
        return OidcUser::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'created_at' => new \DateTimeImmutable(),
            'updated_at' => new \DateTimeImmutable(),
            'iss' => self::faker()->url(),
            'sub' => self::faker()->uuid(),
            'email' => self::faker()->email(),
            'name' => self::faker()->name(),
            'picture_url' => self::faker()->optional()->url(),
            'website_url' => self::faker()->optional()->url(),
        ];
    }

}