<?php

namespace Hyvor\Internal\CloudApi\ConsoleApiAuth;

use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Bundle\Api\DataCarryingHttpException;
use Hyvor\Internal\CloudApi\CloudApiService;
use Hyvor\Internal\CloudApi\Exception\JwtDecodeException;
use Hyvor\Internal\InternalConfig;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @template TResource of object
 */
abstract class ConsoleApiAuthorizationListenerAbstract
{

    public const string ATTRIBUTE_KEY = 'console_auth_results';

    public function __construct(
        private InternalConfig $internalConfig,
        private CloudApiService $cloudApiService,
        private AuthInterface $auth
    ) {}

    /**
     * Return the base path of the Console API. Ex: /api/console/v1
     */
    abstract protected function getBasePath(): string;

    /**
     * which paths should bypass the authorization check.
     * generally, it's /api/console/v1/init (which is only for session-based users and supports loading without an organization)
     * @return string[]
     */
    abstract protected function getBypassPaths(): array;

    /**
     * A way to check if the given bearer token is a resource API key.
     * Can check for a prefix, or a fixed length with hex characters.
     * Note that cloud tokens are JWTs so they start with "eyJ" and have 2 dots in them,
     *      which you can use to distinguish them from resource API keys IF AND ONLY IF you
     *      don't have another way to check for resource API keys (like a prefix or fixed length).
     */
    abstract protected function isResourceApiKey(string $bearerToken): bool;

    /**
     * Given the API key, return
     *  - the resource
     *  - the scopes associated with the API key
     *  - the API key entity
     * Important: must verify that the API key is valid and exists, and should return the resource of that API key.
     *
     * @return null|array{resource: TResource, scopes: string[], apiKey: object}
     */
    abstract protected function getResourceFromApiKey(string $apiKey): null|array;


    /**
     * Simply return the resource from the request.
     * In Hyvor Talk, you would get the website from the request path
     * In Hyvor Post, you would use x-newsletter-id header
     *
     * @param ControllerEvent $event
     * @return TResource|null
     */
    abstract protected function getResourceFromRequest(ControllerEvent $event): ?object;
    protected function getResourceFromRequestError(): string
    {
        // optionally, extend this.
        // for Hyvor Post, better: Unable to find the newsletter from the request. Please provide a valid x-newsletter-id header.
        return 'Unable to find the given resource from the request.';
    }

    /**
     * @param TResource $resource
     */
    abstract protected function getOrganizationIdFromResource(object $resource): int;

    /**
     * Get the user's scopes for the given resource.
     * Return null if the user does not have access to the resource.
     *
     * @param TResource $resource
     * @param int $userId User ID of the user making the request
     * @return null|string[]
     */
    abstract protected function getUserResourceScopes(object $resource, int $userId): null|array;

    protected function onProductApiKeyUse(object $apiKeyModel): void
    {
        // optional to implement
        // can be used to update last accessed time, etc.
    }

    public function __invoke(ControllerEvent $event): void
    {
        if (!str_starts_with($event->getRequest()->getPathInfo(), $this->getBasePath())) {
            return;
        }
        if (in_array($event->getRequest()->getPathInfo(), $this->getBypassPaths(), true)) {
            return;
        }
        if ($event->isMainRequest() === false) {
            return;
        }

        $request = $event->getRequest();
        $authorizationHeader = $request->headers->get('authorization');

        if ($authorizationHeader) {
            if (!str_starts_with($authorizationHeader, 'Bearer ')) {
                throw new AccessDeniedHttpException('Authorization header must start with "Bearer ".');
            }
            $bearerToken = trim(substr($authorizationHeader, 7));

            if ($this->isResourceApiKey($bearerToken)) {
                $authResults = $this->handleResourceApiKey($event, $bearerToken);
            } else {
                $authResults = $this->handleCloudToken($event, $bearerToken);
            }
        } else {
            $authResults = $this->handleSession($event);
        }

        $request->attributes->set(self::ATTRIBUTE_KEY, $authResults);
    }

    private function handleResourceApiKey(ControllerEvent $event, string $apiKey): ConsoleAuthResults
    {
        $orgEndpoint = $event->getAttributes(OrgEndpoint::class)[0] ?? null;

        if ($orgEndpoint) {
            throw new AccessDeniedHttpException(
                'Organization endpoints are not supported with resource API keys. Please use a Cloud API token instead.',
            );
        }

        $resourceAndScopes = $this->getResourceFromApiKey($apiKey);

        if ($resourceAndScopes === null) {
            throw new AccessDeniedHttpException('API key is invalid or does not exist.');
        }

        [
            'resource' => $resource,
            'scopes' => $scopes,
            'apiKey' => $apiKeyEntity
        ] = $resourceAndScopes;

        $this->verifyScopes($scopes, $event);
        $this->onProductApiKeyUse($resourceAndScopes['apiKey']);

        return new ConsoleAuthResults(
            accessType: AccessType::PRODUCT_API_KEY,
            organizationId: $this->getOrganizationIdFromResource($resource),
            resource: $resource,
            productApiKey: $apiKeyEntity
        );
    }

    private function handleCloudToken(ControllerEvent $event, string $jwtToken): ConsoleAuthResults
    {
        $orgEndpoint = $event->getAttributes(OrgEndpoint::class)[0] ?? null;

        try {
            $cloudToken = $this->cloudApiService->decodeJwtToken($jwtToken);
        } catch (JwtDecodeException $e) {
            throw new AccessDeniedHttpException('Invalid Cloud API token: ' . $e->getMessage());
        }

        $this->verifyScopes($cloudToken->getScopesFor($this->internalConfig->getComponent()), $event);

        $resource = null;

        if (!$orgEndpoint) {
            $resource = $this->getResourceFromRequest($event);

            if ($resource === null) {
                throw new AccessDeniedHttpException($this->getResourceFromRequestError());
            }

            if ($this->getOrganizationIdFromResource($resource) !== $cloudToken->getOrganizationId()) {
                throw new AccessDeniedHttpException('does_not_belong_the_resource');
            }
        }

        return new ConsoleAuthResults(
            accessType: AccessType::CLOUD_TOKEN,
            organizationId: $cloudToken->getOrganizationId(),
            resource: $resource,
            jwtSource: $cloudToken->getSource()
        );
    }

    private function handleSession(ControllerEvent $event): ConsoleAuthResults
    {
        $request = $event->getRequest();
        $orgEndpoint = $event->getAttributes(OrgEndpoint::class)[0] ?? null;

        $me = $this->auth->me($request);

        if ($me === null) {
            throw new DataCarryingHttpException(
                401,
                [
                    'login_url' => $this->auth->authUrl('login'),
                    'signup_url' => $this->auth->authUrl('signup'),
                ],
                'Unauthorized',
            );
        }

        $user = $me->getUser();
        $organization = $me->getOrganization();

        if ($organization === null) {
            throw new AccessDeniedHttpException('User does not have a valid current organization, or the organization is not found.');
        }

        // we verify that the organization ID from the frontend matches the organization ID of the user session.
        // prevents two tabs from different organizations from making requests to the same endpoint
        $organizationFromFrontend = (int)$request->headers->get('x-organization-id');

        if ($organizationFromFrontend !== $organization->id) {
            throw new AccessDeniedHttpException('org_mismatch');
        }

        $resource = null;

        if (!$orgEndpoint) {
            $resource = $this->getResourceFromRequest($event);

            if ($resource === null) {
                throw new AccessDeniedHttpException($this->getResourceFromRequestError());
            }

            if ($this->getOrganizationIdFromResource($resource) !== $organization->id) {
                throw new AccessDeniedHttpException('does_not_belong_the_resource');
            }

            $userScopes = $this->getUserResourceScopes($resource, $user->id);

            if ($userScopes === null) {
                throw new AccessDeniedHttpException('You do not have access to this resource.');
            }

            $this->verifyScopes($userScopes, $event);
        }

        return new ConsoleAuthResults(
            accessType: AccessType::SESSION,
            organizationId: $organization->id,
            resource: $resource,
            user: $me
        );
    }

    /**
     * @param string[] $scopes
     */
    private function verifyScopes(array $scopes, ControllerEvent $event): void
    {
        $attributes = $event->getAttributes(ScopeRequired::class);
        $scopeRequiredAttribute = $attributes[0] ?? null;

        assert(
            $scopeRequiredAttribute instanceof ScopeRequired,
            'ScopeRequired attribute must be set for all Console API endpoints.',
        );

        $requiredScope = $scopeRequiredAttribute->scope->value;

        if (!in_array($requiredScope, $scopes, true)) {
            throw new AccessDeniedHttpException(
                "You do not have the required scope '$requiredScope' to access this resource.",
            );
        }
    }

}
