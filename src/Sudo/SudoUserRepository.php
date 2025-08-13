<?php

namespace Hyvor\Internal\Sudo;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Hyvor\Internal\Bundle\Entity\SudoUser;

/**
 * @extends ServiceEntityRepository<SudoUser>
 * @codeCoverageIgnore
 */
class SudoUserRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SudoUser::class);
    }

}