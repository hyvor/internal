<?php

namespace Hyvor\Internal\Bundle\Testing;

use Hyvor\Internal\Bundle\Entity\SudoAuditLog;
use Hyvor\Internal\Sudo\SudoAuditLogRepository;
use Doctrine\ORM\EntityManagerInterface;

trait SudoAuditLogTestingTrait
{
    /**
     * Assert that a SudoAuditLog with the given action and payload exists.
     * @param array<string,scalar> $payload
     */
    public function assertSudoLogged(string $action, array $payload): void
    {
        /** @var EntityManagerInterface $em */
        $em = $this->getContainer()->get(EntityManagerInterface::class);

        /** @var SudoAuditLogRepository $repo */
        $repo = $em->getRepository(SudoAuditLog::class);

        $logs = $repo->findLogs(
            userId: null,
            action: $action,
            dateStart: null,
            dateEnd: null,
            payloadParam: null,
            payloadValue: null,
            limit: null,
            offset: null
        );

        $found = false;

        foreach ($logs as $log) {
            if ($log->getPayload() === $payload) {
                $found = true;
                break;
            }
        }

        $this->assertTrue(
            $found,
            sprintf(
                'Expected SudoAuditLog with action "%s" and payload %s.',
                $action,
                json_encode($payload)
            )
        );
    }
}
