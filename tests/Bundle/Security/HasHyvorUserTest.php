<?php

namespace Hyvor\Internal\Tests\Bundle\Security;

use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Bundle\Security\HasHyvorUser;
use Hyvor\Internal\Tests\SymfonyTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;

#[CoversClass(HasHyvorUser::class)]
class HasHyvorUserTest extends SymfonyTestCase
{

    public function test_has_hyvor_user(): void
    {
        $controller = new HasHyvorUserTestController();
        $user = $controller->getHyvorUser();
        $this->assertEquals(1, $user->id);
    }

}

class HasHyvorUserTestController extends AbstractController
{
    use HasHyvorUser;

    public function getUser(): UserInterface
    {
        return AuthFake::generateUser(['id' => 1]);
    }
}