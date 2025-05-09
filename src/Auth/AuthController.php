<?php

namespace Hyvor\Internal\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * LARAVEL ONLY!
 */
class AuthController
{

    private function getAuthInterface(): AuthInterface
    {
        return app(Auth::class);
    }

    private function getAuth(): Auth
    {
        $auth = $this->getAuthInterface();
        assert($auth instanceof Auth);
        return $auth;
    }

    public function check(): JsonResponse
    {
        $cookie = (string)request()->cookies->get(Auth::HYVOR_SESSION_COOKIE_NAME);
        $user = $this->getAuthInterface()->check($cookie);

        return Response::json([
            'is_logged_in' => $user !== false,
            'user' => $user ? $user : null,
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        return $this->getAuth()->login($this->getRedirect($request));
    }

    public function signup(Request $request): RedirectResponse
    {
        return $this->getAuth()->signup($this->getRedirect($request));
    }

    public function logout(Request $request): RedirectResponse
    {
        return $this->getAuth()->logout($this->getRedirect($request));
    }

    private function getRedirect(Request $request): ?string
    {
        return $request->get('redirect') ?? null;
    }

}
