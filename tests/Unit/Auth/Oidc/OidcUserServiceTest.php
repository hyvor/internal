<?php

namespace Hyvor\Internal\Tests\Unit\Auth\Oidc;

use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Auth\Oidc\OidcUserService;
use Hyvor\Internal\Auth\Oidc\Repository\OidcUserRepository;
use Mockery\MockInterface;

class OidcUserServiceTest
{

    public static function getOidcUserService(): OidcUserService
    {
        /** @var EntityManagerInterface&MockInterface $em */
        $em = \Mockery::mock(EntityManagerInterface::class);

        $oidcUserService = new OidcUserService($em);
        return $oidcUserService;
    }

}