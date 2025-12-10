<?php

namespace Hyvor\Internal\Tests\Unit\Sudo;

use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Bundle\Api\SudoAuthorizationListener;
use Hyvor\Internal\Sudo\SudoUserService;
use Hyvor\Internal\Tests\SymfonyTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

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

    public function test_get_user_from_request(): void
    {
        $authUser = $this->createMock(AuthUser::class);

        $request = new Request();
        $request->attributes->set(
            SudoAuthorizationListener::RESOLVED_USER_ATTRIBUTE_KEY,
            $authUser
        );

        /** @var RequestStack $requestStack */
        $requestStack = $this->container->get(RequestStack::class);
        $requestStack->push($request);

        /** @var SudoUserService $sudoUserService */
        $sudoUserService = $this->container->get(SudoUserService::class);
        $result = $sudoUserService->userFromCurrentRequest();

        $this->assertSame($authUser, $result);
    }

}
