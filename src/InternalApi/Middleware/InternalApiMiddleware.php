<?php

namespace Hyvor\Internal\InternalApi\Middleware;

use Closure;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\Http\Exceptions\HttpException;
use Hyvor\Internal\InternalApi\Exceptions\InvalidMessageException;
use Hyvor\Internal\InternalApi\InternalApi;
use Illuminate\Http\Request;

class InternalApiMiddleware
{

    public function handle(Request $request, Closure $next): mixed
    {
        $toHeader = $request->header('X-Internal-Api-To');

        if (
            !is_string($toHeader) ||
            $toHeader !== Component::current()->value
        ) {
            throw new HttpException('Invalid to component', 403);
        }

        $message = $request->input('message');

        if (!is_string($message)) {
            throw new HttpException('Invalid message');
        }

        try {
            $requestData = InternalApi::dataFromMessage($message);
        } catch (InvalidMessageException $exception) {
            throw new HttpException($exception->getMessage());
        }

        $request->replace($requestData);

        return $next($request);
    }

}
