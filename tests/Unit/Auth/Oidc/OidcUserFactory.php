<?php

namespace Hyvor\Internal\Tests\Unit\Auth\Oidc;

use Hyvor\Internal\Bundle\Entity\OidcUser;

trait OidcUserFactory
{

    private function createOidcUser(
        string $email = 'test@hyvor.com',
        string $name = 'Test User',
        string $iss = 'https://example.com',
        string $sub = '1234567890',
        ?string $picture = null,
        ?string $website = null
    ): OidcUser {
        $oidcUser = new OidcUser();
        $oidcUser->setEmail($email);
        $oidcUser->setName($name);
        $oidcUser->setIss($iss);
        $oidcUser->setSub($sub);
        $oidcUser->setPictureUrl($picture);
        $oidcUser->setWebsiteUrl($website);
        $oidcUser->setCreatedAt(new \DateTimeImmutable());
        $oidcUser->setUpdatedAt(new \DateTimeImmutable());

        return $oidcUser;
    }

}