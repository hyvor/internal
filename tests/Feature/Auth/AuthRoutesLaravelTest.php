<?php

namespace Hyvor\Internal\Tests\Feature\Auth;

use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\InternalServiceProvider;
use Hyvor\Internal\Laravel\AuthController;
use Hyvor\Internal\Tests\LaravelTestCase;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AuthController::class)]
class AuthRoutesLaravelTest extends LaravelTestCase
{

    public function testDoesNotAddRoutesIfDisabled(): void
    {
        config(['internal.auth.routes' => false]);
        Route::setRoutes(new RouteCollection());

        $app = $this->app;
        assert($app !== null);
        (new InternalServiceProvider($app))->boot();
        $this->get('/api/auth/check')->assertNotFound();
    }

    public function testCheckWhenNotLoggedIn(): void
    {
        AuthFake::enable(null);

        $this
            ->post('/api/auth/check')
            ->assertOk()
            ->assertJsonPath('is_logged_in', false)
            ->assertJsonPath('user', null);
    }

    public function testCheckWhenLoggedIn(): void
    {
        AuthFake::enable(['id' => 1]);

        $this
            ->post('/api/auth/check')
            ->assertJsonPath('is_logged_in', true)
            ->assertJsonPath('user.id', 1);
    }

    public function testRedirects(): void
    {
        $this
            ->get('/api/auth/login')
            ->assertRedirectContains('https://hyvor.com/login?redirect=');

        $this
            ->get('/api/auth/signup')
            ->assertRedirectContains('https://hyvor.com/signup?redirect=');

        $this
            ->get('/api/auth/logout')
            ->assertRedirectContains('https://hyvor.com/logout?redirect=');
    }

    public function testRespectsDomain(): void
    {
        AuthFake::enable(null);

        $app = $this->app;
        assert($app !== null);

        $app->register(InternalServiceProvider::class, true);

        config(['internal.auth.routes_domain' => 'hyvor.cluster']);
        Route::setRoutes(new RouteCollection());
        (new InternalServiceProvider($app))->boot();

        $this
            ->post('https://hyvor.com/api/auth/check')
            ->assertStatus(404);

        $this
            ->post('https://hyvor.cluster/api/auth/check')
            ->assertOk();
    }

}
