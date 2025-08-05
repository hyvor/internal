<?php

namespace Hyvor\Internal\Auth\Oidc\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Hyvor\Internal\Bundle\Entity\OidcUser;

/**
 * @extends ServiceEntityRepository<OidcUser>
 */
class OidcUserRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OidcUser::class);
    }

}