<?php

namespace Hyvor\Internal\Sudo;

use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Bundle\Entity\SudoAuditLog;
use Symfony\Component\Clock\ClockAwareTrait;

class SudoAuditLogService
{

    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private SudoUserService $sudoUserService
    ) {
    }

    /**
     * Use snake case for action ex: 'cancel_subscription'
     * @param string $action
     * @param array<string,scalar> $payload
     * @param ?AuthUser $user
     */
    public function log(string $action, array $payload, ?AuthUser $user = null): void {
        if ($user == null) {
            $user = $this->sudoUserService->userFromCurrentRequest();
        }

        $auditLog = new SudoAuditLog();
        $auditLog->setUserId($user->id);
        $auditLog->setAction($action);
        $auditLog->setPayload($payload);
        $auditLog->setCreatedAt($this->now());
        $auditLog->setUpdatedAt($this->now());

        $this->em->persist($auditLog);
        $this->em->flush();
    }
}
