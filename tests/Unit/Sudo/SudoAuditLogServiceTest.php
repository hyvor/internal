<?php

namespace Hyvor\Internal\Tests\Unit\Sudo;

use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Bundle\Api\SudoAuthorizationListener;
use Hyvor\Internal\Bundle\Entity\SudoAuditLog;
use Hyvor\Internal\Bundle\Testing\SudoAuditLogTestingTrait;
use Hyvor\Internal\Sudo\SudoAuditLogService;
use Hyvor\Internal\Tests\SymfonyTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

#[CoversClass(SudoAuditLogService::class)]
class SudoAuditLogServiceTest extends SymfonyTestCase
{
    use SudoAuditLogTestingTrait;

    public function test_log_persists_audit_log(): void
    {
        $authUser = $this->createMock(AuthUser::class);
        $authUser->id = 1;
        $authUser->username = "user";
        $authUser->name = "User";
        $authUser->email = "user@hyvor.com";

        $request = new Request();
        $request->attributes->set(
            SudoAuthorizationListener::RESOLVED_USER_ATTRIBUTE_KEY,
            $authUser
        );

        /** @var RequestStack $requestStack */
        $requestStack = $this->container->get(RequestStack::class);
        $requestStack->push($request);

        /** @var SudoAuditLogService $service */
        $service = $this->container->get(SudoAuditLogService::class);

        $service->log(
            "cancel_subscription",
            ["reason" => "expired"],
            null
        );

        $this->assertSudoLogged("cancel_subscription", ["reason" => "expired"]);

        $logs = $this->em
            ->getRepository(SudoAuditLog::class)
            ->findAll();

        $this->assertCount(1, $logs);

        $log = $logs[0];

        $this->assertSame(1, $log->getUserId());
        $this->assertSame("cancel_subscription", $log->getAction());
        $this->assertSame(["reason" => "expired"], $log->getPayload());
    }

    public function test_find_logs_filters_by_user_and_action(): void
    {
        $authUser = $this->createMock(AuthUser::class);
        $authUser->id = 1;
        $authUser->username = "user";
        $authUser->name = "User";
        $authUser->email = "user@hyvor.com";

        $request = new Request();
        $request->attributes->set(
            SudoAuthorizationListener::RESOLVED_USER_ATTRIBUTE_KEY,
            $authUser
        );

        /** @var RequestStack $requestStack */
        $requestStack = $this->container->get(RequestStack::class);
        $requestStack->push($request);

        /** @var SudoAuditLogService $service */
        $service = $this->container->get(SudoAuditLogService::class);

        $service->log("renew_trial", ["period_days" => 14], null);
        $service->log("renew_trial", ["period_days" => 7], null);
        $service->log("upgrade_plan", ["to" => "business"], null);

        $results = $service->findLogs(
            userId: null,
            action: "renew_trial",
            dateStart: null,
            dateEnd: null,
            payloadParam: null,
            payloadValue: null,
            limit: null,
            offset: null
        );

        $this->assertCount(2, $results);
    }
}
