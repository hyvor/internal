<?php

namespace Hyvor\Internal\Tests\Unit\CloudApi\CloudApiAuth;

use Hyvor\Internal\CloudApi\ConsoleApiAuth\AccessType;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\ConsoleApiAuthorizationListenerAbstract;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\ConsoleAuthResults;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\OrgEndpoint;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\ScopeRequired;
use Hyvor\Internal\CloudApi\Scope\PostScope;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

#[CoversClass(ConsoleApiAuthorizationListenerAbstract::class)]
#[CoversClass(ConsoleAuthResults::class)]
#[CoversClass(ScopeRequired::class)]
class ResourceApiKeyAuthTest extends TestCase
{

    use ConsoleApiAuthTrait;

    public function test_fails_on_org_endpoint(): void
    {
        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessageIsOrContains('Organization endpoints are not supported with resource API keys');

        $this->invokeListener(
            controllerAttributes: [
                new OrgEndpoint()
            ],
            headers: [
                'Authorization' => 'Bearer resource_1',
            ]
        );
    }

    public function test_when_api_key_is_invalid(): void
    {
        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessageIsOrContains('API key is invalid or does not exist.');

        $this->invokeListener(
            headers: [
                'Authorization' => 'Bearer resource_1',
            ],
        );
    }

    public function test_when_scopes_are_not_included(): void
    {
        $listener = $this->createListener(
            getResourceFromApiKey: function(string $apiKey): array {
                return [
                    'resource' => new TestResource(),
                    'scopes' => ['newsletter.read'],
                    'apiKey' => new TestResourceApiKey()
                ];
            }
        );

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessageIsOrContains('You do not have the required scope \'newsletter.write\' to access this resource.');

        $this->invokeListener(
            controllerAttributes: [
                new ScopeRequired(PostScope::NEWSLETTER_WRITE)
            ],
            listener: $listener,
            headers: [
                'Authorization' => 'Bearer resource_1',
            ],
        );
    }

    public function test_works_with_all(): void
    {

        $productApiKeyUsed = false;
        $resource = new TestResource();
        $listener = $this->createListener(
            getResourceFromApiKey: function(string $apiKey) use (&$resource): array {
                $this->assertSame('resource_1', $apiKey);
                return [
                    'resource' => $resource,
                    'scopes' => ['newsletter.read', 'newsletter.write'],
                    'apiKey' => new TestResourceApiKey()
                ];
            },
            onProductApiKeyUse: function(object $apiKeyModel) use (&$productApiKeyUsed): void {
                $productApiKeyUsed = true;
                $this->assertInstanceOf(TestResourceApiKey::class, $apiKeyModel);
            },
            organizationId: 13
        );

        $request = $this->invokeListener(
            controllerAttributes: [
                new ScopeRequired(PostScope::NEWSLETTER_WRITE)
            ],
            listener: $listener,
            headers: [
                'Authorization' => 'Bearer resource_1',
            ],
        );

        $this->assertTrue($productApiKeyUsed);

        $consoleAuthResults = $request->attributes->get('console_auth_results');
        $this->assertInstanceOf(ConsoleAuthResults::class, $consoleAuthResults);

        $this->assertSame(AccessType::PRODUCT_API_KEY, $consoleAuthResults->getAccessType());
        $this->assertSame(13, $consoleAuthResults->getOrganizationId());
        $this->assertSame($resource, $consoleAuthResults->getResource());
        $this->assertNull($consoleAuthResults->getNullableUser());
        $this->assertSame('product_api:123', $consoleAuthResults->getSourceString());

    }

}
