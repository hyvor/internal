<?php

namespace Hyvor\Internal\Tests\Unit\CloudApi\CloudApiAuth;

use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Auth\Dto\Me;
use Hyvor\Internal\CloudApi\CloudApiService;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\ConsoleApiAuthorizationListenerAbstract;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\InternalConfig;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel;
use Closure;

trait ConsoleApiAuthTrait
{

    protected function createListener(
        null|Closure $getResourceFromApiKey = null,
        null|Closure $onProductApiKeyUse = null,
        int $organizationId = 1,
        ?CloudApiService $cloudApiService  = null,
        Component $component = Component::POST,
        ?object $resourceFromRequest = null,
        ?AuthInterface $auth = null,
        null|Closure $getUserResourceScopes = null,
    ): ConsoleApiAuthorizationListenerAbstract
    {

        $internalConfig = $this->createStub(InternalConfig::class);
        $internalConfig
            ->method('getComponent')
            ->willReturn($component);
        $cloudApiService ??= $this->createMock(CloudApiService::class);
        $auth ??= $this->createStub(AuthInterface::class);

        return new class(
            $internalConfig,
            $cloudApiService,
            $auth,
            $getResourceFromApiKey,
            $onProductApiKeyUse,
            $organizationId,
            $resourceFromRequest,
            $getUserResourceScopes,
        ) extends ConsoleApiAuthorizationListenerAbstract
        {

            public function __construct(
                InternalConfig $internalConfig,
                CloudApiService $cloudApiService,
                AuthInterface $auth,
                private null|Closure $getResourceFromApiKey = null,
                private null|Closure $onProductApiKeyUse = null,
                private int $organizationId = 1,
                private ?object $resourceFromRequest = null,
                private null|Closure $getUserResourceScopes = null,
            ) {
                parent::__construct($internalConfig, $cloudApiService, $auth);
            }

            protected function getBasePath(): string
            {
                return '/api/console';
            }

            protected function getBypassPaths(): array
            {
                return [
                    '/api/console/init',
                ];
            }

            protected function isResourceApiKey(string $bearerToken): bool
            {
                return str_starts_with($bearerToken, 'resource_');
            }

            protected function getResourceFromApiKey(string $apiKey): null|array
            {
                if ($this->getResourceFromApiKey) {
                    return ($this->getResourceFromApiKey)($apiKey);
                }
                return null;
            }

            protected function getResourceFromRequest(ControllerEvent $event): ?object
            {
                return $this->resourceFromRequest;
            }

            protected function getOrganizationIdFromResource(object $resource): int
            {
                return $this->organizationId;
            }

            protected function getUserResourceScopes(object $resource, int $userId): null|array
            {
                if ($this->getUserResourceScopes) {
                    return ($this->getUserResourceScopes)($resource, $userId);
                }
                return null;
            }

            protected function onProductApiKeyUse(object $apiKeyModel): void
            {
                if ($this->onProductApiKeyUse) {
                    ($this->onProductApiKeyUse)($apiKeyModel);
                }
            }
        };

    }

    protected function invokeListener(
        array $controllerAttributes = [],
        string $path = '/api/console/test',
        ?ConsoleApiAuthorizationListenerAbstract $listener = null,
        array $headers = []
    ): Request
    {
        $listener ??= $this->createListener();

        $request = Request::create($path);
        foreach ($headers as $key => $value) {
            $request->headers->set($key, $value);
        }

        $event = new ControllerEvent(
            $this->createStub(HttpKernelInterface::class),
            fn() => null,
            $request,
            Kernel::MAIN_REQUEST
        );

        $event->setController(fn() => null, $controllerAttributes);
        $listener->__invoke($event);

        return $request;
    }

}
