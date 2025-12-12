<?php

namespace Hyvor\Internal\Sudo;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Hyvor\Internal\Bundle\Entity\SudoAuditLog;

/**
 * @extends ServiceEntityRepository<SudoAuditLog>
 * @codeCoverageIgnore
 */
class SudoAuditLogRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SudoAuditLog::class);
    }

}
