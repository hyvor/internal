<?php

namespace Hyvor\Internal\Bundle\Api;

use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Bundle\Api\DataCarryingHttpException;
use Hyvor\Internal\Sudo\SudoUserService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::CONTROLLER, priority: 200)]
class SudoAuthorizationListener
{
    const string RESOLVED_USER_ATTRIBUTE_KEY = 'sudo_api_resolved_user';
    const string RESOLVED_SUDO_USER_ATTRIBUTE_KEY = 'sudo_api_resolved_sudo_user';

    public function __construct(
        private AuthInterface $auth,
        private SudoUserService $sudoUserService
    ) {
    }

    // can be extended
    protected function getPath(): string
    {
        return '/api/sudo';
    }

    public function __invoke(ControllerEvent $event): void
    {
        if (!str_starts_with($event->getRequest()->getPathInfo(), $this->getPath())) {
            return;
        }

        $request = $event->getRequest();
        $me = $this->auth->me($request);

        if (!$me) {
            throw new DataCarryingHttpException(
                403,
                [
                    'login_url' => $this->auth->authUrl('login'),
                ],
                'Invalid session.'
            );
        }

        $user = $me->getUser();
        $sudoUser = $this->sudoUserService->get($user->id);

        if ($sudoUser === null) {
            throw new AccessDeniedHttpException('You do not have sudo access.');
        }

        $request->attributes->set(self::RESOLVED_USER_ATTRIBUTE_KEY, $user);
        $request->attributes->set(self::RESOLVED_SUDO_USER_ATTRIBUTE_KEY, $sudoUser);
    }
}
