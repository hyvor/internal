<?php

namespace Unit\CloudApi\CloudApiAuth;

use Hyvor\Internal\CloudApi\CloudApiService;
use Hyvor\Internal\CloudApi\CloudJwt;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\AccessType;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\ConsoleApiAuthorizationListenerAbstract;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\ConsoleAuthResults;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\OrgEndpoint;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\ScopeRequired;
use Hyvor\Internal\CloudApi\Exception\JwtDecodeException;
use Hyvor\Internal\CloudApi\JwtSource\JwtSource;
use Hyvor\Internal\CloudApi\Scope\PostScope;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\Tests\Unit\CloudApi\CloudApiAuth\ConsoleApiAuthTrait;
use Hyvor\Internal\Tests\Unit\CloudApi\CloudApiAuth\TestResource;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

#[CoversClass(ConsoleApiAuthorizationListenerAbstract::class)]
#[CoversClass(ConsoleAuthResults::class)]
#[CoversClass(ScopeRequired::class)]
class CloudTokenAuthTest extends TestCase
{

    use ConsoleApiAuthTrait;

    public function test_fails_when_decoding_fails(): void
    {
        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessageIsOrContains('Invalid Cloud API token: token invalid');

        $cloudApiService = $this->createMock(CloudApiService::class);
        $cloudApiService->method('decodeJwtToken')->willThrowException(new JwtDecodeException('token invalid'));

        $listener = $this->createListener(
            cloudApiService: $cloudApiService,
        );

        $this->invokeListener(
            listener: $listener,
            headers: [
                'Authorization' => 'Bearer invalid_token',
            ],
        );
    }

    public function test_fails_when_scopes_are_not_included(): void
    {
        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessageIsOrContains('You do not have the required scope \'newsletter.write\' to access this resource.');

        $cloudJwtToken = CloudJwt::fromArray([
            'iss' => 'https://api.hyvor.com',
            'sub' => '1',
            'iat' => (string) time(),
            'nbf' => (string) time(),
            'exp' => (string) (time() + 3600),
            'scope' => 'post:newsletter.read',
            'src' => JwtSource::forCloud('apikey')->getSource(),
        ]);

        $cloudApiService = $this->createMock(CloudApiService::class);
        $cloudApiService->method('decodeJwtToken')->willReturn($cloudJwtToken);

        $listener = $this->createListener(
            cloudApiService: $cloudApiService,
        );

        $this->invokeListener(
            controllerAttributes: [
                new ScopeRequired(PostScope::NEWSLETTER_WRITE),
            ],
            listener: $listener,
            headers: [
                'Authorization' => 'Bearer valid_token',
            ],
        );
    }

    public function test_succeeds_for_org_endpoint(): void
    {
        $cloudJwtToken = CloudJwt::fromArray([
            'iss' => 'https://api.hyvor.com',
            'sub' => '14',
            'iat' => (string) time(),
            'nbf' => (string) time(),
            'exp' => (string) (time() + 3600),
            'scope' => 'post:newsletter.read post:newsletter.write',
            'src' => JwtSource::forInternal(Component::BLOGS)->getSource(),
        ]);

        $cloudApiService = $this->createMock(CloudApiService::class);
        $cloudApiService->method('decodeJwtToken')->willReturn($cloudJwtToken);

        $listener = $this->createListener(
            cloudApiService: $cloudApiService,
        );

        $request = $this->invokeListener(
            controllerAttributes: [
                new ScopeRequired(PostScope::NEWSLETTER_WRITE),
                new OrgEndpoint(),
            ],
            listener: $listener,
            headers: [
                'Authorization' => 'Bearer valid_token',
            ],
        );

        $consoleAuthResults = $request->attributes->get('console_auth_results');
        $this->assertInstanceOf(ConsoleAuthResults::class, $consoleAuthResults);

        $this->assertSame(AccessType::CLOUD_TOKEN, $consoleAuthResults->getAccessType());
        $this->assertSame(14, $consoleAuthResults->getOrganizationId());
        $this->assertSame(null, $consoleAuthResults->getResource());
        $this->assertNull($consoleAuthResults->getNullableUser());
        $this->assertSame('cloud_api:internal:blogs', $consoleAuthResults->getSourceString());

    }

    public function test_succeeds_for_resource_endpoint(): void
    {
        $cloudJwtToken = CloudJwt::fromArray([
            'iss' => 'https://api.hyvor.com',
            'sub' => '14',
            'iat' => (string) time(),
            'nbf' => (string) time(),
            'exp' => (string) (time() + 3600),
            'scope' => 'post:newsletter.read post:newsletter.write',
            'src' => JwtSource::forCloud('key-1')->getSource(),
        ]);

        $cloudApiService = $this->createMock(CloudApiService::class);
        $cloudApiService->method('decodeJwtToken')->willReturn($cloudJwtToken);

        $resource = new TestResource();
        $listener = $this->createListener(
            organizationId: 14,
            cloudApiService: $cloudApiService,
            resourceFromRequest: $resource
        );

        $request = $this->invokeListener(
            controllerAttributes: [
                new ScopeRequired(PostScope::NEWSLETTER_WRITE),
            ],
            listener: $listener,
            headers: [
                'Authorization' => 'Bearer valid_token',
            ],
        );

        $consoleAuthResults = $request->attributes->get('console_auth_results');
        $this->assertInstanceOf(ConsoleAuthResults::class, $consoleAuthResults);

        $this->assertSame(AccessType::CLOUD_TOKEN, $consoleAuthResults->getAccessType());
        $this->assertSame(14, $consoleAuthResults->getOrganizationId());
        $this->assertSame($resource, $consoleAuthResults->getResource());
        $this->assertNull($consoleAuthResults->getNullableUser());
        $this->assertSame('cloud_api:cloud:key-1', $consoleAuthResults->getSourceString());
    }

    public function test_fails_when_resource_not_found_from_request(): void
    {
        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessageIsOrContains('Unable to find the given resource from the request.');

        $cloudJwtToken = CloudJwt::fromArray([
            'iss' => 'https://api.hyvor.com',
            'sub' => '14',
            'iat' => (string) time(),
            'nbf' => (string) time(),
            'exp' => (string) (time() + 3600),
            'scope' => 'post:newsletter.read post:newsletter.write',
            'src' => JwtSource::forCloud('key-1')->getSource(),
        ]);

        $cloudApiService = $this->createMock(CloudApiService::class);
        $cloudApiService->method('decodeJwtToken')->willReturn($cloudJwtToken);

        $listener = $this->createListener(
            cloudApiService: $cloudApiService,
            resourceFromRequest: null,
        );

        $this->invokeListener(
            controllerAttributes: [
                new ScopeRequired(PostScope::NEWSLETTER_WRITE),
            ],
            listener: $listener,
            headers: [
                'Authorization' => 'Bearer valid_token',
            ],
        );
    }

    public function test_fails_when_organization_id_from_resource_does_not_match_token(): void
    {
        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessageIsOrContains('does_not_belong_the_resource');

        $cloudJwtToken = CloudJwt::fromArray([
            'iss' => 'https://api.hyvor.com',
            'sub' => '14',
            'iat' => (string) time(),
            'nbf' => (string) time(),
            'exp' => (string) (time() + 3600),
            'scope' => 'post:newsletter.read post:newsletter.write',
            'src' => JwtSource::forCloud('key-1')->getSource(),
        ]);

        $cloudApiService = $this->createMock(CloudApiService::class);
        $cloudApiService->method('decodeJwtToken')->willReturn($cloudJwtToken);

        $resource = new TestResource();
        $listener = $this->createListener(
            organizationId: 15, // different org id than token
            cloudApiService: $cloudApiService,
            resourceFromRequest: $resource
        );

        $this->invokeListener(
            controllerAttributes: [
                new ScopeRequired(PostScope::NEWSLETTER_WRITE),
            ],
            listener: $listener,
            headers: [
                'Authorization' => 'Bearer valid_token',
            ],
        );
    }

}
