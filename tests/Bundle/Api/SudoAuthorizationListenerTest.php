<?php

namespace Hyvor\Internal\Tests\Bundle\Api;

use Hyvor\Internal\Bundle\Api\SudoPermissionRequired;
use Hyvor\Internal\Tests\Bundle\Api\Enum\TestSudoPermissionEnum;
use Hyvor\Internal\Tests\Bundle\Api\Enum\TestSudoRoleEnum;
use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Bundle\Api\DataCarryingHttpException;
use Hyvor\Internal\Bundle\Api\SudoAuthorizationListener;
use Hyvor\Internal\Bundle\Entity\SudoUser;
use Hyvor\Internal\InternalConfig;
use Hyvor\Internal\Tests\Helper\UpdatesInternalConfig;
use Hyvor\Internal\Tests\SymfonyTestCase;
use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Sudo\SudoUserService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

#[CoversClass(SudoAuthorizationListener::class)]
class SudoAuthorizationListenerTest extends SymfonyTestCase
{

    use UpdatesInternalConfig;

    private function createSudoServiceMock(): SudoUserService&MockObject
    {
        return $this->createMock(SudoUserService::class);
    }

    /**
     * @param array<class-string, list<object>>|null $attributes
     */
    private function createEvent(string $path, ?array $attributes = null): ControllerEvent
    {
        $request = Request::create($path);
        $event = new ControllerEvent(
            $this->kernel,
            static fn() => null,
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        if ($attributes) {
            $event->setController(static fn() => null, $attributes);
        }

        return $event;
    }

    private function getListener(SudoUserService $sudoUserService): SudoAuthorizationListener
    {
        $this->updateInternalConfig('sudoPermissionEnum', TestSudoPermissionEnum::class);
        $this->updateInternalConfig('sudoRoleEnum', TestSudoRoleEnum::class);

        /** @var AuthInterface $auth */
        $auth = $this->container->get(AuthInterface::class);

        /** @var InternalConfig $internalConfig */
        $internalConfig = $this->container->get(InternalConfig::class);

        /** @var RequestStack $requestStack */
        $requestStack = $this->container->get(RequestStack::class);

        return new SudoAuthorizationListener(
            $auth,
            $sudoUserService,
            $internalConfig,
            $requestStack
        );
    }

    public function test_ignores_non_sudo_api_requests(): void
    {
        $sudoService = $this->createSudoServiceMock();
        $sudoService->expects($this->never())->method('get');

        $listener = $this->getListener($sudoService);
        $event = $this->createEvent('/api/console/some-endpoint');

        $listener($event);

        $this->assertFalse($event->getRequest()->attributes->has(SudoAuthorizationListener::RESOLVED_USER_ATTRIBUTE_KEY));
    }

    public function test_when_no_permission_attributes_set_in_controller(): void
    {
        AuthFake::enableForSymfony($this->container, [
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $sudoService = $this->createSudoServiceMock();

        $sudoUser = (new SudoUser())->setUserId(1);
        $sudoService->expects($this->once())
            ->method('get')
            ->with(1)
            ->willReturn($sudoUser);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('This sudo route has not implemented required permissions');

        $event = $this->createEvent('/api/sudo/some-endpoint');
        $listener = $this->getListener($sudoService);
        $listener($event);
    }

    public function test_when_no_required_permissions(): void
    {
        AuthFake::enableForSymfony($this->container, [
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $sudoService = $this->createSudoServiceMock();

        $sudoUser = (new SudoUser())
            ->setUserId(1)
            ->setRole('support');
        $sudoService->expects($this->once())
            ->method('get')
            ->with(1)
            ->willReturn($sudoUser);

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('Your sudo role does not have permission to access this resource.');

        $event = $this->createEvent('/api/sudo/dangerous-endpoint', [
            SudoPermissionRequired::class => [
                new SudoPermissionRequired(TestSudoPermissionEnum::DELETE_EVERYTHING)
            ]
        ]);
        $listener = $this->getListener($sudoService);
        $listener($event);
    }

    public function test_sudo_api_access_with_valid_sudo_user(): void
    {
        AuthFake::enableForSymfony($this->container, [
            'id' => 1,
            'name' => 'Test User', 
            'email' => 'test@example.com'
        ]);

        $sudoService = $this->createSudoServiceMock();

        $sudoUser = (new SudoUser())
            ->setUserId(1)
            ->setRole('sudo');
        $sudoService->expects($this->once())
            ->method('get')
            ->with(1) 
            ->willReturn($sudoUser);


        $event = $this->createEvent('/api/sudo/some-endpoint', [
            SudoPermissionRequired::class => [
                new SudoPermissionRequired(TestSudoPermissionEnum::DELETE_EVERYTHING)
            ]
        ]);
        $listener = $this->getListener($sudoService);
        $listener($event);

        $resolvedUser = $event->getRequest()->attributes->get(SudoAuthorizationListener::RESOLVED_USER_ATTRIBUTE_KEY);
        $resolvedSudoUser = $event->getRequest()->attributes->get(SudoAuthorizationListener::RESOLVED_SUDO_USER_ATTRIBUTE_KEY);

        $this->assertEquals(1, $resolvedUser->id);
        $this->assertSame($sudoUser, $resolvedSudoUser);
    }

    public function test_sudo_api_access_with_guest_user(): void
    {
        AuthFake::enableForSymfony($this->container, null);
        $sudoService = $this->createSudoServiceMock();

        $listener = $this->getListener($sudoService);
        $event = $this->createEvent('/api/sudo/some-endpoint');
        $event->getRequest()->cookies->set('authsess', 'validSession');

        try {
            $listener($event);
            $this->fail('Expected DataCarryingHttpException to be thrown');
        } catch (DataCarryingHttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
            $this->assertSame('auth_required', $e->getMessage());

            $data = $e->getData();
            $this->assertArrayHasKey('login_url', $data);
        }
    }

    public function test_sudo_api_access_with_invalid_sudo_user(): void
    {
        AuthFake::enableForSymfony($this->container, [
            'id' => 1,
            'name' => 'Test User', 
            'email' => 'test@example.com'
        ]);
        $sudoService = $this->createSudoServiceMock();

        $sudoService->expects($this->once())
            ->method('get')
            ->with(1)
            ->willReturn(null);

        $listener = $this->getListener($sudoService);
        $event = $this->createEvent('/api/sudo/some-endpoint');

        try {
            $listener($event);
            $this->fail('Expected AccessDeniedHttpException to be thrown');
        } catch (AccessDeniedHttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
            $this->assertSame('You do not have sudo access.', $e->getMessage());
        }
    }

    public function test_sudo_api_access_without_session(): void
    {
        AuthFake::enableForSymfony($this->container, null);
        $sudoService = $this->createSudoServiceMock();

        $listener = $this->getListener($sudoService);
        $event = $this->createEvent('/api/sudo/some-endpoint');

        try {
            $listener($event);
            $this->fail('Expected DataCarryingHttpException to be thrown');
        } catch (DataCarryingHttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
            $this->assertSame('auth_required', $e->getMessage());
        }
    }
}
