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

    /**
     * @param ?int $userId
     * @param ?string $action
     * @param ?\DateTimeImmutable $dateStart
     * @param ?\DateTimeImmutable $dateEnd
     * @param ?string $payloadParam
     * @param ?scalar $payloadValue
     * @param ?int $limit
     * @param ?int $offset
     * @return SudoAuditLog[]
     */
    public function findLogs(
        ?int $userId,
        ?string $action,
        ?\DateTimeImmutable $dateStart,
        ?\DateTimeImmutable $dateEnd,
        ?string $payloadParam,
        mixed $payloadValue,
        ?int $limit,
        ?int $offset
    ): array {
    $qb = $this->createQueryBuilder("s");

    if ($userId !== null) {
        $qb->andWhere("s.user_id = :userId")
           ->setParameter("userId", $userId);
    }

    if ($action !== null) {
        $qb->andWhere("s.action = :action")
           ->setParameter("action", $action);
    }

    if ($dateStart !== null) {
        $qb->andWhere("s.created_at >= :dateStart")
           ->setParameter("dateStart", $dateStart);
    }

    if ($dateEnd !== null) {
        $qb->andWhere("s.created_at <= :dateEnd")
           ->setParameter("dateEnd", $dateEnd);
    }

    if ($payloadParam !== null && $payloadValue !== null) {
        $qb->andWhere("s.payload @> :payloadFilter")
            ->setParameter(
                "payloadFilter",
                json_encode([$payloadParam => $payloadValue])
            );
    }

    $qb->orderBy("s.created_at", "DESC");

    if ($limit !== null) {
        $qb->setMaxResults($limit);
    }

    if ($offset !== null) {
        $qb->setFirstResult($offset);
    }

    return $qb->getQuery()->getResult();
    }

}
