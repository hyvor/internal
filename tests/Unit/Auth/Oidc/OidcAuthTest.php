<?php

namespace Hyvor\Internal\Tests\Unit\Auth\Oidc;

use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Auth\Oidc\OidcAuth;
use Hyvor\Internal\Auth\Oidc\OidcUserService;
use Hyvor\Internal\Auth\Oidc\Repository\OidcUserRepository;
use Hyvor\Internal\Bundle\Entity\OidcUser;
use Hyvor\Internal\Tests\SymfonyTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

#[CoversClass(OidcAuth::class)]
#[CoversClass(OidcUserService::class)]
#[CoversClass(OidcUserRepository::class)]
#[CoversClass(OidcUser::class)]
#[CoversClass(AuthUser::class)]
class OidcAuthTest extends SymfonyTestCase
{

    use OidcUserFactoryTrait;

    private function getOidcAuth(): OidcAuth
    {
        $oidcAuth = $this->container->get(OidcAuth::class);
        assert($oidcAuth instanceof OidcAuth);
        return $oidcAuth;
    }

    public function test_check_not_logged_in(): void
    {
        $oidc = $this->getOidcAuth();
        $requestNotLoggedIn = Request::create('/');
        $requestNotLoggedIn->setSession($this->createMock(SessionInterface::class));
        $this->assertNull($oidc->me($requestNotLoggedIn));
    }

    public function test_check_has_session_but_wrong_user_id(): void
    {
        $oidc = $this->getOidcAuth();
        $session = $this->createMock(SessionInterface::class);
        $session
            ->method('get')
            ->with('oidc_user_id')
            ->willReturn(1);

        $requestNotLoggedIn = Request::create('/');
        $requestNotLoggedIn->setSession($session);

        $this->assertNull($oidc->me($requestNotLoggedIn));
    }

    public function test_check_logged_in(): void
    {
        $oidcUser = $this->createOidcUser();
        $this->em->persist($oidcUser);
        $this->em->flush();

        $oidc = $this->getOidcAuth();
        $session = $this->createMock(SessionInterface::class);
        $session
            ->method('get')
            ->with('oidc_user_id')
            ->willReturn($oidcUser->getId());

        $requestNotLoggedIn = Request::create('/');
        $requestNotLoggedIn->setSession($session);

        $me = $oidc->me($requestNotLoggedIn);
        $this->assertNotNull($me);

        $user = $me->getUser();
        $this->assertSame($oidcUser->getId(), $user->id);
        $this->assertSame($oidcUser->getEmail(), $user->email);
        $this->assertSame($oidcUser->getName(), $user->name);
        $this->assertSame($oidcUser->getPictureUrl(), $user->picture_url);
        $this->assertSame($oidcUser->getWebsiteUrl(), $user->website_url);
    }

    public function test_auth_url(): void
    {
        $oidcAuth = $this->getOidcAuth();

        // Test login URL
        $loginUrl = $oidcAuth->authUrl('login');
        $this->assertSame('/api/oidc/login', $loginUrl);

        // Test logout URL
        $logoutUrl = $oidcAuth->authUrl('logout');
        $this->assertEquals('/api/oidc/logout', $logoutUrl);

        // Test with custom redirect
        $customRedirect = '/custom-page';
        $loginUrlWithRedirect = $oidcAuth->authUrl('login', $customRedirect);
        $this->assertStringContainsString('redirect=%2Fcustom-page', $loginUrlWithRedirect);
    }

    public function test_from_id(): void
    {
        $user = $this->createOidcUser();
        $this->em->persist($user);
        $this->em->flush();

        $oidcAuth = $this->getOidcAuth();
        $fetchedUser = $oidcAuth->fromId($user->getId());
        $this->assertInstanceOf(AuthUser::class, $fetchedUser);
        $this->assertSame($user->getId(), $fetchedUser->id);

        $otherUser = $oidcAuth->fromId(103484123);
        $this->assertNull($otherUser);
    }

    public function test_from_ids(): void
    {
        $user1 = $this->createOidcUser(sub: '1');
        $user2 = $this->createOidcUser(sub: '2');
        $this->em->persist($user1);
        $this->em->persist($user2);
        $this->em->flush();

        $oidcAuth = $this->getOidcAuth();
        $fetchedUsers = $oidcAuth->fromIds([$user1->getId(), $user2->getId()]);

        $this->assertCount(2, $fetchedUsers);
        $this->assertArrayHasKey($user1->getId(), $fetchedUsers);
        $this->assertArrayHasKey($user2->getId(), $fetchedUsers);
    }

    public function test_from_email(): void
    {
        $user1 = $this->createOidcUser(sub: '1');
        $user2 = $this->createOidcUser(sub: '2');
        $user3 = $this->createOidcUser(sub: '3', email: 'other@hyvor.com');

        $this->em->persist($user1);
        $this->em->persist($user2);
        $this->em->persist($user3);
        $this->em->flush();

        $oidcAuth = $this->getOidcAuth();
        $fetchedUsers = $oidcAuth->fromEmail('test@hyvor.com');

        $this->assertCount(2, $fetchedUsers);
        $this->assertContainsOnlyInstancesOf(AuthUser::class, $fetchedUsers);

        $this->assertSame($user1->getId(), $fetchedUsers[0]->id);
        $this->assertSame($user2->getId(), $fetchedUsers[1]->id);
    }

    public function test_from_emails(): void
    {
        $email1User1 = $this->createOidcUser(sub: '1', email: "one@hyvor.com");
        $email1User2 = $this->createOidcUser(sub: '2', email: 'one@hyvor.com');

        $email2User1 = $this->createOidcUser(sub: '3', email: "two@hyvor.com");

        $email3User1 = $this->createOidcUser(sub: '4', email: "three@hyvor.com");

        $this->em->persist($email1User1);
        $this->em->persist($email1User2);
        $this->em->persist($email2User1);
        $this->em->persist($email3User1);

        $this->em->flush();

        $oidcAuth = $this->getOidcAuth();
        $fetchedUsers = $oidcAuth->fromEmails(['one@hyvor.com', 'two@hyvor.com']);
        $this->assertCount(2, $fetchedUsers);

        $one = $fetchedUsers['one@hyvor.com'];
        $this->assertCount(2, $one);
        $this->assertContainsOnlyInstancesOf(AuthUser::class, $one);
        $this->assertSame($email1User1->getId(), $one[0]->id);
        $this->assertSame($email1User2->getId(), $one[1]->id);

        $two = $fetchedUsers['two@hyvor.com'];
        $this->assertCount(1, $two);
        $this->assertContainsOnlyInstancesOf(AuthUser::class, $two);
        $this->assertSame($email2User1->getId(), $two[0]->id);
    }

}
