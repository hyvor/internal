<?php

namespace Hyvor\Internal\Tests\Unit\Sudo;

use Hyvor\Internal\Sudo\SudoUserService;
use Hyvor\Internal\Tests\SymfonyTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SudoUserService::class)]
class SudoUserServiceTest extends SymfonyTestCase
{

    use SudoUserFactoryTrait;

    public function test_get_all(): void
    {
        $user1 = $this->createSudoUser(1, em: $this->em);
        $user2 = $this->createSudoUser(2, em: $this->em);

        /** @var SudoUserService $sudoUserService */
        $sudoUserService = $this->container->get(SudoUserService::class);
        $sudoUsers = $sudoUserService->getAll();

        $this->assertCount(2, $sudoUsers);
        $this->assertContains($user1, $sudoUsers);
        $this->assertContains($user2, $sudoUsers);
    }

}