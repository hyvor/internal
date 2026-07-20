<?php

namespace Unit\CloudApi;

use Hyvor\Internal\Auth\Oidc\Testing\OidcTestingUtils;
use Hyvor\Internal\Bundle\Comms\CommsInterface;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\CloudApi\GetJwtToken;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\CloudApi\GetJwtTokenResponse;
use Hyvor\Internal\Bundle\Comms\MockComms;
use Hyvor\Internal\CloudApi\CloudApiService;
use Hyvor\Internal\CloudApi\CloudJwt;
use Hyvor\Internal\CloudApi\InternalCloudApiTokenProvider;
use Hyvor\Internal\CloudApi\JwtSource\JwtSource;
use Hyvor\Internal\CloudApi\Scope\PostScope;
use Hyvor\Internal\CloudApi\Scope\ScopeBuilder;
use Hyvor\Internal\CloudApi\Scope\TalkScope;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\Tests\SymfonyTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(CloudApiService::class)]
#[CoversClass(CloudJwt::class)]
#[CoversClass(InternalCloudApiTokenProvider::class)]
class CloudApiServiceTest extends SymfonyTestCase
{

    use ClockSensitiveTrait;

    public function test_create_jwt_token(): void
    {

        $this->mockTime('2024-06-01 12:00:00');

        /** @var CloudApiService $cloudApiService */
        $cloudApiService = $this->getContainer()->get(CloudApiService::class);

        $scopeBuilder = new ScopeBuilder();
        $scopeBuilder->addScopes(Component::TALK, [TalkScope::WEBSITE_READ]);

        $jwtToken = $cloudApiService->createJwtToken(10, $scopeBuilder, JwtSource::forCloud('cloud-api-key'));

        $this->assertSame(10, $jwtToken->getOrganizationId());
        $this->assertSame('2024-06-01 13:00:00', new \DateTimeImmutable('@' . $jwtToken->getExpiresAt())->format('Y-m-d H:i:s'));

        $this->assertSame(
            [
                'iss' => 'https://hyvor.com',
                'sub' => '10',
                'iat' => 1717243200,
                'nbf' => 1717243200,
                'exp' => 1717246800,
                'scope' => 'talk:website.read',
                'src' => 'cloud:cloud-api-key',
            ],
            $jwtToken->toArray()
        );

    }


    public function test_decode_jwt_token_from_http_jwks(): void
    {
        $keys = OidcTestingUtils::generateKey('cloud-api-key');

        $httpResponse = new JsonMockResponse($keys['jwks']);
        $this->getContainer()
            ->set(HttpClientInterface::class, new MockHttpClient($httpResponse));

        /** @var CloudApiService $cloudApiService */
        $cloudApiService = $this->getContainer()->get(CloudApiService::class);

        $scopeBuilder = new ScopeBuilder();
        $scopeBuilder->addScopes(Component::POST, [PostScope::ORG_NEWSLETTERS_CREATE, PostScope::ORG_NEWSLETTERS_READ]);
        $jwtToken = $cloudApiService
            ->createJwtToken(10, $scopeBuilder, JwtSource::forCloud('cloud-api-key'))
            ->encode($keys['privateKeyPem'], 'cloud-api-key');

        $decodedJwt = $cloudApiService->decodeJwtToken($jwtToken);
        $this->assertSame(10, $decodedJwt->getOrganizationId());
        $this->assertSame(['org.newsletters.create', 'org.newsletters.read'], $decodedJwt->getScopesFor(Component::POST));
        $this->assertSame([], $decodedJwt->getScopesFor(Component::TALK));
    }

    public function test_sdk_client_for_org(): void
    {
        $httpResponse = new JsonMockResponse([]);
        $this->getContainer()
            ->set(HttpClientInterface::class, new MockHttpClient($httpResponse));

        /** @var CloudApiService $cloudApiService */
        $cloudApiService = $this->getContainer()->get(CloudApiService::class);

        /** @var MockComms $mockComms */
        $mockComms = $this->getContainer()->get(CommsInterface::class);
        $mockComms->addResponse(
            GetJwtToken::class,
            new GetJwtTokenResponse(
                'eyJhbGciOi',
                new \DateTimeImmutable('+1 hour')
            )
        );

        $client = $cloudApiService->getHyvorClientForOrganization(
            10,
            Component::POST,
            [PostScope::NEWSLETTER_READ]
        );

        $client->post->newsletter(1)->issues->list();

        $this->assertSame(
            'https://post.hyvor.internal/api/console/issues',
            $httpResponse->getRequestUrl()
        );

        $this->assertContains(
            'Authorization: Bearer eyJhbGciOi',
            $httpResponse->getRequestOptions()['headers']
        );

        // make sure it's cached
        $cacheKey = 'cloud_api_token_post_10_' . md5('newsletter.read');
        /** @var CacheItemPoolInterface $cache */
        $cache = $this->getContainer()->get(CacheItemPoolInterface::class);
        $cacheItem = $cache->getItem($cacheKey);
        $this->assertTrue($cacheItem->isHit());
    }

    public function test_sdk_client_cached_for_org(): void
    {
        $httpResponse = new JsonMockResponse([]);
        $this->getContainer()
            ->set(HttpClientInterface::class, new MockHttpClient($httpResponse));


        /** @var CacheItemPoolInterface $cache */
        $cache = $this->getContainer()->get(CacheItemPoolInterface::class);

        $cacheKey = 'cloud_api_token_post_10_' . md5('issues.read');
        $cacheItem = $cache->getItem($cacheKey);
        $cacheItem->set('token-from-cache');
        $cache->save($cacheItem);

        /** @var CloudApiService $cloudApiService */
        $cloudApiService = $this->getContainer()->get(CloudApiService::class);
        $client = $cloudApiService->getHyvorClientForOrganization(
            10,
            Component::POST,
            [PostScope::ISSUES_READ]
        );
        $client->post->newsletter(1)->issues->list();

        $this->assertSame(
            'https://post.hyvor.internal/api/console/issues',
            $httpResponse->getRequestUrl()
        );

        $this->assertContains(
            'Authorization: Bearer token-from-cache',
            $httpResponse->getRequestOptions()['headers']
        );
    }

}
