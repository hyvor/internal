<?php

namespace Hyvor\Internal\Bundle\Api;

use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Bundle\Entity\SudoUser;
use Hyvor\Internal\InternalConfig;
use Hyvor\Internal\Sudo\SudoPermissionInterface;
use Hyvor\Internal\Sudo\SudoUserService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::CONTROLLER, priority: 200)]
class SudoAuthorizationListener
{
    const string RESOLVED_USER_ATTRIBUTE_KEY = 'sudo_api_resolved_user';
    const string RESOLVED_SUDO_USER_ATTRIBUTE_KEY = 'sudo_api_resolved_sudo_user';
    const string RESOLVED_SUDO_PERMISSIONS_ATTRIBUTE_KEY = 'sudo_api_resolved_sudo_permissions';

    public function __construct(
        private AuthInterface $auth,
        private SudoUserService $sudoUserService,
        private InternalConfig $internalConfig,
        private RequestStack $requestStack,
    ) {
    }

    /**
     * Get the paths to protect with sudo auth.
     * Protects all subpaths of it.
     * Prefix all paths with /
     * @return string[]
     */
    protected function getSudoPaths(): array
    {
        return [
            '/api/sudo',
            '/api/messenger', // messenger UI
        ];
    }

    protected function getUserFromRequest(Request $request): ?AuthUser
    {
        return $this->auth->me($request)?->getUser();
    }

    private function isSudoPath(string $path): bool
    {
        foreach ($this->getSudoPaths() as $sudoPath) {
            if (str_starts_with($path, $sudoPath)) {
                return true;
            }
        }

        return false;
    }

    public function __invoke(ControllerEvent $event): void
    {
        if (!$this->isSudoPath($event->getRequest()->getPathInfo())) {
            return;
        }

        if ($event->isMainRequest() === false) {
            return;
        }

        $request = $event->getRequest();
        $user = $this->getUserFromRequest($request);

        if (!$user) {
            throw new DataCarryingHttpException(
                403,
                [
                    'login_url' => $this->auth->authUrl('login'),
                ],
                'auth_required'
            );
        }

        $sudoUser = $this->sudoUserService->get($user->id);

        if ($sudoUser === null) {
            throw new AccessDeniedHttpException('You do not have sudo access.');
        }

        $role = $sudoUser->getRole();

        $roleEnumClass = $this->internalConfig->getSudoRoleEnum();
        assert(
            $roleEnumClass !== null,
            'sudo.role_enum is not set in internal config'
        );

        $roleEnum = $roleEnumClass::from($role);
        $rolePermissions = $roleEnum->getPermissions();

        /** @var ?SudoPermissionRequired $requiredPermission */
        $requiredPermission = $event->getAttributes(SudoPermissionRequired::class)[0] ?? null;

        if ($requiredPermission === null) {
            throw new HttpException(500, 'This sudo route has not implemented required permissions');
        }

        if (!in_array(
            $requiredPermission->getPermission(),
            $rolePermissions
        )) {
            throw new AccessDeniedHttpException('Your sudo role does not have permission to access this resource.');
        }

        $request->attributes->set(self::RESOLVED_USER_ATTRIBUTE_KEY, $user);
        $request->attributes->set(self::RESOLVED_SUDO_USER_ATTRIBUTE_KEY, $sudoUser);
        $request->attributes->set(self::RESOLVED_SUDO_PERMISSIONS_ATTRIBUTE_KEY, $rolePermissions);
    }

    public function getResolvedUser(): AuthUser
    {
        $request = $this->requestStack->getCurrentRequest();
        assert($request !== null);

        $user = $request->attributes->get(self::RESOLVED_USER_ATTRIBUTE_KEY);
        assert($user instanceof AuthUser);

        return $user;
    }

    public function getResolvedSudoUser(): SudoUser
    {
        $request = $this->requestStack->getCurrentRequest();
        assert($request !== null);

        $sudoUser = $request->attributes->get(self::RESOLVED_SUDO_USER_ATTRIBUTE_KEY);
        assert($sudoUser instanceof SudoUser);

        return $sudoUser;
    }

    /**
     * @return array<\BackedEnum & SudoPermissionInterface>
     */
    public function getResolvedPermissions(): array
    {
        $request = $this->requestStack->getCurrentRequest();
        assert($request !== null);

        $permissions = $request->attributes->get(self::RESOLVED_SUDO_PERMISSIONS_ATTRIBUTE_KEY);
        assert(is_array($permissions));

        return $permissions;
    }



}
