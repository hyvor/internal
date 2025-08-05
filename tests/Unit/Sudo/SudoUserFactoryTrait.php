<?php

namespace Hyvor\Internal\Tests\Unit\Sudo;

use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Bundle\Entity\SudoUser;

trait SudoUserFactoryTrait
{

    // using foundry in tests did not work for some reason
    // so manually creating SudoUser instances
    private function createSudoUser(int $userId = 1, ?EntityManagerInterface $em = null): SudoUser
    {
        $sudoUser = new SudoUser();
        $sudoUser->setUserId($userId);
        $sudoUser->setCreatedAt(new \DateTimeImmutable());
        $sudoUser->setUpdatedAt(new \DateTimeImmutable());

        if ($em) {
            $em->persist($sudoUser);
            $em->flush();
        }

        return $sudoUser;
    }

}