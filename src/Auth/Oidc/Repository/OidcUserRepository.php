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

    /**
     * @return OidcUser[]
     */
    public function searchByQuery(string $query, int $limit = 10): array
    {
        return $this->createQueryBuilder('u')
            ->where('LOWER(u.email) LIKE LOWER(:query) OR LOWER(u.name) LIKE LOWER(:query)')
            ->setParameter('query', '%' . $query . '%')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
