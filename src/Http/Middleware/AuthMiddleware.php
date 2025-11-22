<?php

namespace Hyvor\Internal\Http\Middleware;

use Closure;
use Hyvor\Internal\Auth\Auth;
use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Http\Exceptions\HttpException;
use Illuminate\Http\Request;

/**
 * @deprecated keep middleware at the product level. Do not use this for new development.
 */
class AuthMiddleware
{

    public function handle(Request $request, Closure $next): mixed
    {
        $user = app(AuthInterface::class)->check($request);

        if (!$user) {
            throw new HttpException('Unauthorized', 401);
        }

        $vars = get_object_vars($user);
        $vars['current_organization'] = (array)$user->current_organization;
        // @phpstan-ignore-next-line
        $accessUser = AccessAuthUser::fromArray($vars);
        app()->instance(AccessAuthUser::class, $accessUser);

        return $next($request);
    }

}
