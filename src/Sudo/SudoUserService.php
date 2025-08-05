<?php

namespace Hyvor\Internal\Sudo;

use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Bundle\Entity\SudoUser;
use Symfony\Component\Clock\ClockAwareTrait;

class SudoUserService
{

    use ClockAwareTrait;

    public function __construct(
        private SudoUserRepository $sudoUserRepository,
        private EntityManagerInterface $em,
    ) {
    }

    public function exists(int $userId): bool
    {
        return $this->sudoUserRepository->find($userId) !== null;
    }

    public function get(int $userId): ?SudoUser
    {
        return $this->sudoUserRepository->find($userId);
    }

    public function create(int $userId): void
    {
        $user = new SudoUser();
        $user->setUserId($userId);
        $user->setCreatedAt($this->now());
        $user->setUpdatedAt($this->now());

        $this->em->persist($user);
        $this->em->flush();
    }

    public function remove(SudoUser $user): void
    {
        $this->em->remove($user);
        $this->em->flush();
    }

}