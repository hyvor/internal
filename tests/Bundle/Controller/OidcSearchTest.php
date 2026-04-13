<?php

namespace Hyvor\Internal\Tests\Bundle\Controller;

use Hyvor\Internal\Bundle\Controller\OidcController;
use Hyvor\Internal\Bundle\Entity\OidcUser;
use Hyvor\Internal\Tests\SymfonyTestCase;
use Hyvor\Internal\Tests\Unit\Auth\Oidc\OidcUserFactoryTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[CoversClass(OidcController::class)]
class OidcSearchTest extends SymfonyTestCase
{

    use OidcUserFactoryTrait;

    private function createLoggedInRequest(OidcUser $user, string $search = ''): Request
    {
        $request = Request::create('/api/oidc/search', 'GET', ['search' => $search]);
        $session = new Session(new MockArraySessionStorage());
        $session->set('oidc_user_id', $user->getId());
        $request->setSession($session);
        return $request;
    }

    public function test_throws_not_found_when_not_on_prem(): void
    {
        $_ENV['DEPLOYMENT'] = 'cloud';

        $request = Request::create('/api/oidc/search');

        $this->expectException(NotFoundHttpException::class);
        try {
            $this->kernel->handle($request, catch: false);
        } finally {
            unset($_ENV['DEPLOYMENT']);
        }
    }

    public function test_throws_access_denied_when_not_logged_in(): void
    {
        $request = Request::create('/api/oidc/search');
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);

        $this->expectException(AccessDeniedHttpException::class);
        $this->kernel->handle($request, catch: false);
    }

    public function test_searches_by_name(): void
    {
        $user = $this->createOidcUser(email: 'actor@example.com', name: 'John Actor', em: $this->em);
        $this->createOidcUser(email: 'other@example.com', name: 'Jane Smith', sub: 'other-sub', em: $this->em);

        $request = $this->createLoggedInRequest($user, 'Actor');
        $response = $this->kernel->handle($request, catch: false);

        $this->assertSame(200, $response->getStatusCode());

        $data = json_decode((string) $response->getContent(), true);
        $this->assertCount(1, $data);
        $this->assertSame('John Actor', $data[0]['user_username']);
        $this->assertSame('actor@example.com', $data[0]['user_email']);
        $this->assertSame('admin', $data[0]['role']);
    }

    public function test_searches_by_email(): void
    {
        $user = $this->createOidcUser(email: 'find@example.com', name: 'Test User', em: $this->em);
        $this->createOidcUser(email: 'other@example.com', name: 'Other User', sub: 'other-sub', em: $this->em);

        $request = $this->createLoggedInRequest($user, 'find@example');
        $response = $this->kernel->handle($request, catch: false);

        $this->assertSame(200, $response->getStatusCode());

        $data = json_decode((string) $response->getContent(), true);
        $this->assertCount(1, $data);
        $this->assertSame('find@example.com', $data[0]['user_email']);
        $this->assertSame('Test User', $data[0]['user_username']);
    }
}
