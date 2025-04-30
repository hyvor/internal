<?php

namespace Hyvor\Internal\Bundle\Security;

use Hyvor\Internal\Auth\AuthUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

trait HasHyvorUser
{

    public function getHyvorUser(): AuthUser
    {
        assert($this instanceof AbstractController);
        $user = $this->getUser();
        assert($user instanceof AuthUser);

        return $user;
    }

}