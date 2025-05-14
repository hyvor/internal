<?php

namespace Hyvor\Internal\Tests\Bundle\Security;

use Hyvor\Internal\Bundle\Security\HasHyvorUser;
use Hyvor\Internal\Tests\SymfonyTestCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HasHyvorUserTest extends SymfonyTestCase
{

    public function test_has_hyvor_user(): void
    {
        $controller = new HasHyvorUserTestController();
        $controller->setContainer($this->getContainer());
        $user = $controller->getHyvorUser();

        $this->assertInstanceOf(HasHyvorUserTestController::class, $controller);
        $this->assertInstanceOf(AuthUser::class, $user);
    }

}

class HasHyvorUserTestController extends AbstractController
{
    use HasHyvorUser;
}