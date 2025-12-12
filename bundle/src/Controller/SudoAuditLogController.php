<?php

namespace Hyvor\Internal\Bundle\Controller;

use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Sudo\SudoAuditLogService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class SudoAuditLogController extends AbstractController
{

    public function __construct(
        private SudoAuditLogService $sudoAuditLogService,
        private AuthInterface $auth
    ) {
    }

    #[Route("/api/sudo/audit-logs", methods: "GET")]
    public function getAuditLogs(Request $request): JsonResponse
    {
        $limit = $request->query->getInt("limit", 50);
        $offset = $request->query->getInt("offset", 0);
        $userId = $request->query->getInt("user_id") ?: null;
        $action = $request->query->getString("action") ?: null;
        $dateStart = $request->query->getInt("date_start") ?: null;
        $dateEnd = $request->query->getInt("date_end") ?: null;
        $payloadParam = $request->query->getString("payload_param") ?: null;
        $payloadValue = $request->query->getString("payload_value") ?: null;

        if ($limit > 100 || $limit < 1) {
            throw new BadRequestException("limit should be between 1 and 100");
        }

        if ($offset < 0) {
            throw new BadRequestException("offset should not be less than 0");
        }

        if (($payloadParam != null && $payloadValue == null) || ($payloadValue != null && $payloadParam == null)) {
            throw new BadRequestException("payload_param and payload_value are both required");
        }

        $dateStart = $dateStart !== null ? (new \DateTimeImmutable())->setTimestamp($dateStart) : null;
        $dateEnd   = $dateEnd !== null ? (new \DateTimeImmutable())->setTimestamp($dateEnd) : null;

        if ($dateStart && $dateStart < new \DateTimeImmutable('@0')) {
            throw new BadRequestException("date_start cannot be negative");
        }

        if ($dateEnd && $dateEnd < new \DateTimeImmutable('@0')) {
            throw new BadRequestException("date_end cannot be negative");
        }

        if ($dateStart && $dateEnd && $dateStart >= $dateEnd) {
            throw new BadRequestException("date_start must be before date_end");
        }

        $logs = $this->sudoAuditLogService->findLogs(
            $userId,
            $action,
            $dateStart,
            $dateEnd,
            $payloadParam,
            $payloadValue,
            $limit,
            $offset
        );

        $userIds = array_unique(array_map(fn($log) => $log->getUserId(), $logs));
        $users = $this->auth->fromIds($userIds);

        $data = array_map(fn($log) => [
            "id" => $log->getId(),
            "user" => $users[$log->getUserId()] ?? null,
            "action" => $log->getAction(),
            "payload" => $log->getPayload(),
            "created_at" => $log->getCreatedAt()->format("c"),
            "updated_at" => $log->getUpdatedAt()->format("c"),
        ], $logs);

        return $this->json($data);
    }
}
