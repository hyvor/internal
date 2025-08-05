<?php

namespace Hyvor\Internal\Tests\Unit\Auth\Oidc;

use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Bundle\Entity\OidcUser;

trait OidcUserFactoryTrait
{

    private function createOidcUser(
        string $email = 'test@hyvor.com',
        string $name = 'Test User',
        string $iss = 'https://example.com',
        string $sub = '1234567890',
        ?string $picture = null,
        ?string $website = null,
        ?EntityManagerInterface $em = null,
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

        if ($em) {
            $em->persist($oidcUser);
            $em->flush();
        }

        return $oidcUser;
    }

}