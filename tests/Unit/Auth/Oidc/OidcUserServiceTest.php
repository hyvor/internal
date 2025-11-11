<?php

namespace Hyvor\Internal\Tests\Unit\Auth\Oidc;

use Hyvor\Internal\Auth\Oidc\OidcUserService;
use Hyvor\Internal\Tests\SymfonyTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(OidcUserService::class)]
class OidcUserServiceTest extends SymfonyTestCase
{

    use OidcUserFactoryTrait;

    public function test_get_total_users_count(): void
    {
        $this->createOidcUser('supun@hyvor.com', sub: '1', em: $this->em);
        $this->createOidcUser('ishini@hyvor.com', sub: '2', em: $this->em);

        $oidcUserService = $this->container->get(OidcUserService::class);
        assert($oidcUserService instanceof OidcUserService);

        $usersCount = $oidcUserService->getTotalUserCount();
        $this->assertEquals(2, $usersCount);
    }

}