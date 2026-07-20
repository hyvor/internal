<?php

namespace Unit\CloudApi\CloudApiAuth;

use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Auth\AuthUserOrganization;
use Hyvor\Internal\Auth\Dto\Me;
use Hyvor\Internal\Bundle\Api\DataCarryingHttpException;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\AccessType;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\ConsoleApiAuthorizationListenerAbstract;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\ConsoleAuthResults;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\OrgEndpoint;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\ScopeRequired;
use Hyvor\Internal\CloudApi\Scope\PostScope;
use Hyvor\Internal\Tests\Unit\CloudApi\CloudApiAuth\ConsoleApiAuthTrait;
use Hyvor\Internal\Tests\Unit\CloudApi\CloudApiAuth\TestResource;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

#[CoversClass(ConsoleApiAuthorizationListenerAbstract::class)]
#[CoversClass(ConsoleAuthResults::class)]
#[CoversClass(ScopeRequired::class)]
class SessionAuthTest extends TestCase
{

    use ConsoleApiAuthTrait;

    private function createMe(int $userId = 1, ?AuthUserOrganization $organization = null, int $sessionId = 100): Me
    {
        return new Me(
            new AuthUser(
                id: $userId,
                username: 'user',
                name: 'User',
                email: 'user@hyvor.com',
            ),
            $organization,
            $sessionId,
        );
    }

    public function test_fails_when_not_logged_in(): void
    {
        $auth = $this->createMock(AuthInterface::class);
        $auth->method('me')->willReturn(null);
        $auth->method('authUrl')->willReturnCallback(fn(string $page): string => match ($page) {
            'login' => 'https://hyvor.com/login',
            'signup' => 'https://hyvor.com/signup',
            default => throw new \LogicException("Unexpected page: $page"),
        });

        $listener = $this->createListener(auth: $auth);

        try {
            $this->invokeListener(
                listener: $listener,
            );
            $this->fail('Expected DataCarryingHttpException to be thrown.');
        } catch (DataCarryingHttpException $e) {
            $this->assertSame(401, $e->getStatusCode());
            $this->assertSame('Unauthorized', $e->getMessage());
            $this->assertSame([
                'login_url' => 'https://hyvor.com/login',
                'signup_url' => 'https://hyvor.com/signup',
            ], $e->getData());
        }
    }

    public function test_fails_when_no_current_organization(): void
    {
        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessageIsOrContains(
            'User does not have a valid current organization, or the organization is not found.'
        );

        $auth = $this->createMock(AuthInterface::class);
        $auth->method('me')->willReturn($this->createMe(organization: null));

        $listener = $this->createListener(auth: $auth);

        $this->invokeListener(
            listener: $listener,
        );
    }

    public function test_fails_when_organization_header_does_not_match_session_organization(): void
    {
        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessageIsOrContains('org_mismatch');

        $auth = $this->createMock(AuthInterface::class);
        $auth->method('me')->willReturn(
            $this->createMe(organization: new AuthUserOrganization(1, 'Org', 'admin'))
        );

        $listener = $this->createListener(auth: $auth);

        $this->invokeListener(
            listener: $listener,
            headers: [
                'x-organization-id' => '2',
            ],
        );
    }

    public function test_succeeds_for_org_endpoint(): void
    {
        $auth = $this->createMock(AuthInterface::class);
        $auth->method('me')->willReturn(
            $this->createMe(
                userId: 43,
                organization: new AuthUserOrganization(1, 'Org', 'admin'),
                sessionId: 50
            )
        );

        $listener = $this->createListener(auth: $auth);

        $request = $this->invokeListener(
            controllerAttributes: [
                new OrgEndpoint(),
            ],
            listener: $listener,
            headers: [
                'x-organization-id' => '1',
            ],
        );

        $consoleAuthResults = $request->attributes->get('console_auth_results');
        $this->assertInstanceOf(ConsoleAuthResults::class, $consoleAuthResults);

        $this->assertSame(AccessType::SESSION, $consoleAuthResults->getAccessType());
        $this->assertSame(1, $consoleAuthResults->getOrganizationId());
        $this->assertSame(null, $consoleAuthResults->getResource());
        $this->assertNotNull($consoleAuthResults->getNullableUser());
        $this->assertSame(43, $consoleAuthResults->getNullableUser()->id);
        $this->assertSame('session:50', $consoleAuthResults->getSourceString());
    }

    public function test_fails_when_resource_not_found_from_request(): void
    {
        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessageIsOrContains('Unable to find the given resource from the request.');

        $auth = $this->createMock(AuthInterface::class);
        $auth->method('me')->willReturn(
            $this->createMe(organization: new AuthUserOrganization(1, 'Org', 'admin'))
        );

        $listener = $this->createListener(
            auth: $auth,
            resourceFromRequest: null,
        );

        $this->invokeListener(
            listener: $listener,
            headers: [
                'x-organization-id' => '1',
            ],
        );
    }

    public function test_fails_when_organization_id_from_resource_does_not_match_session_organization(): void
    {
        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessageIsOrContains('does_not_belong_the_resource');

        $auth = $this->createMock(AuthInterface::class);
        $auth->method('me')->willReturn(
            $this->createMe(organization: new AuthUserOrganization(1, 'Org', 'admin'))
        );

        $listener = $this->createListener(
            auth: $auth,
            organizationId: 15, // different than the session's organization id
            resourceFromRequest: new TestResource(),
        );

        $this->invokeListener(
            listener: $listener,
            headers: [
                'x-organization-id' => '1',
            ],
        );
    }

    public function test_fails_when_user_does_not_have_access_to_resource(): void
    {
        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessageIsOrContains('You do not have access to this resource.');

        $auth = $this->createMock(AuthInterface::class);
        $auth->method('me')->willReturn(
            $this->createMe(organization: new AuthUserOrganization(1, 'Org', 'admin'))
        );

        $listener = $this->createListener(
            auth: $auth,
            organizationId: 1,
            resourceFromRequest: new TestResource(),
            getUserResourceScopes: fn(object $resource, int $userId): ?array => null,
        );

        $this->invokeListener(
            listener: $listener,
            headers: [
                'x-organization-id' => '1',
            ],
        );
    }

    public function test_fails_when_scopes_are_not_included(): void
    {
        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessageIsOrContains(
            'You do not have the required scope \'newsletter.write\' to access this resource.'
        );

        $auth = $this->createMock(AuthInterface::class);
        $auth->method('me')->willReturn(
            $this->createMe(organization: new AuthUserOrganization(1, 'Org', 'admin'))
        );

        $listener = $this->createListener(
            auth: $auth,
            organizationId: 1,
            resourceFromRequest: new TestResource(),
            getUserResourceScopes: fn(object $resource, int $userId): array => ['newsletter.read'],
        );

        $this->invokeListener(
            controllerAttributes: [
                new ScopeRequired(PostScope::NEWSLETTER_WRITE),
            ],
            listener: $listener,
            headers: [
                'x-organization-id' => '1',
            ],
        );
    }

    public function test_succeeds_for_resource_endpoint(): void
    {
        $resource = new TestResource();

        $auth = $this->createMock(AuthInterface::class);
        $auth->method('me')->willReturn(
            $this->createMe(userId: 5, organization: new AuthUserOrganization(1, 'Org', 'admin'), sessionId: 200)
        );

        $listener = $this->createListener(
            auth: $auth,
            organizationId: 1,
            resourceFromRequest: $resource,
            getUserResourceScopes: function (object $resource, int $userId): array {
                $this->assertSame(5, $userId);
                return ['newsletter.read', 'newsletter.write'];
            },
        );

        $request = $this->invokeListener(
            controllerAttributes: [
                new ScopeRequired(PostScope::NEWSLETTER_WRITE),
            ],
            listener: $listener,
            headers: [
                'x-organization-id' => '1',
            ],
        );

        $consoleAuthResults = $request->attributes->get('console_auth_results');
        $this->assertInstanceOf(ConsoleAuthResults::class, $consoleAuthResults);

        $this->assertSame(AccessType::SESSION, $consoleAuthResults->getAccessType());
        $this->assertSame(1, $consoleAuthResults->getOrganizationId());
        $this->assertSame($resource, $consoleAuthResults->getResource());
        $this->assertNotNull($consoleAuthResults->getNullableUser());
        $this->assertSame('session:200', $consoleAuthResults->getSourceString());
    }

}
