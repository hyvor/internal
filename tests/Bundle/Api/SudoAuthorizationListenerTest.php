<?php

namespace Hyvor\Internal\Tests\Bundle\Api;

use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Bundle\Api\DataCarryingHttpException;
use Hyvor\Internal\Bundle\Api\SudoAuthorizationListener;
use Hyvor\Internal\Bundle\Entity\SudoUser;
use Hyvor\Internal\Tests\SymfonyTestCase;
use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Sudo\SudoUserService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

#[CoversClass(SudoAuthorizationListener::class)]
class SudoAuthorizationListenerTest extends SymfonyTestCase
{
    private function createSudoServiceMock(): SudoUserService&MockObject
    {
        return $this->createMock(SudoUserService::class);
    }

    private function createEvent(string $path): ControllerEvent
    {
        $request = Request::create($path);
        return new ControllerEvent(
            $this->kernel,
            static fn() => null,
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );
    }

    public function test_ignores_non_sudo_api_requests(): void
    {
        /** @var AuthInterface $auth */
        $auth = $this->container->get(AuthInterface::class);
        $sudoService = $this->createSudoServiceMock();

        $sudoService->expects($this->never())->method('get');

        $listener = new SudoAuthorizationListener($auth, $sudoService);
        $event = $this->createEvent('/api/console/some-endpoint');

        $listener($event);

        $this->assertFalse($event->getRequest()->attributes->has(SudoAuthorizationListener::RESOLVED_USER_ATTRIBUTE_KEY));
    }

    public function test_sudo_api_access_with_valid_sudo_user(): void
    {
        AuthFake::enableForSymfony($this->container, [
            'id' => 1,
            'name' => 'Test User', 
            'email' => 'test@example.com'
        ]);

        /** @var AuthInterface $auth */
        $auth = $this->container->get(AuthInterface::class);
        $sudoService = $this->createSudoServiceMock();

        $sudoUser = (new SudoUser())->setUserId(1);
        $sudoService->expects($this->once())
            ->method('get')
            ->with(1) 
            ->willReturn($sudoUser);


        $event = $this->createEvent('/api/sudo/some-endpoint');
        $listener = new SudoAuthorizationListener($auth, $sudoService);
        $listener($event);

        $resolvedUser = $event->getRequest()->attributes->get(SudoAuthorizationListener::RESOLVED_USER_ATTRIBUTE_KEY);
        $resolvedSudoUser = $event->getRequest()->attributes->get(SudoAuthorizationListener::RESOLVED_SUDO_USER_ATTRIBUTE_KEY);

        $this->assertEquals(1, $resolvedUser->id);
        $this->assertSame($sudoUser, $resolvedSudoUser);
    }

    public function test_sudo_api_access_with_guest_user(): void
    {
        AuthFake::enableForSymfony($this->container, null);

        /** @var AuthInterface $auth */
        $auth = $this->container->get(AuthInterface::class);
        $sudoService = $this->createSudoServiceMock();

        $listener = new SudoAuthorizationListener($auth, $sudoService);
        $event = $this->createEvent('/api/sudo/some-endpoint');
        $event->getRequest()->cookies->set('authsess', 'validSession');

        try {
            $listener($event);
            $this->fail('Expected DataCarryingHttpException to be thrown');
        } catch (DataCarryingHttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
            $this->assertSame('Invalid session.', $e->getMessage());

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

        /** @var AuthInterface $auth */
        $auth = $this->container->get(AuthInterface::class);
        $sudoService = $this->createSudoServiceMock();

        $sudoService->expects($this->once())
            ->method('get')
            ->with(1)
            ->willReturn(null);

        $listener = new SudoAuthorizationListener($auth, $sudoService);
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

        /** @var AuthInterface $auth */
        $auth = $this->container->get(AuthInterface::class);
        $sudoService = $this->createSudoServiceMock();

        $listener = new SudoAuthorizationListener($auth, $sudoService);
        $event = $this->createEvent('/api/sudo/some-endpoint');

        try {
            $listener($event);
            $this->fail('Expected DataCarryingHttpException to be thrown');
        } catch (DataCarryingHttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
            $this->assertSame('Invalid session.', $e->getMessage());
        }
    }
}
