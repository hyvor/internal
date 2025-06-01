<?php

namespace Hyvor\Internal\Resource;

use Carbon\Carbon;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\InternalApi\InternalApi;
use Hyvor\Internal\InternalApi\InternalApiMethod;

class Resource
{

    public function __construct(private InternalApi $internalApi)
    {
    }

    public function register(
        int $userId,
        int $resourceId,
        ?Carbon $at = null
    ): void {
        $this->internalApi->call(
            Component::CORE,
            '/resource/register',
            [
                'user_id' => $userId,
                'resource_id' => $resourceId,
                'at' => $at?->getTimestamp(),
            ]
        );
    }

    public function delete(int $resourceId): void
    {
        $this->internalApi->call(
            Component::CORE,
            '/resource/delete',
            [
                'resource_id' => $resourceId,
            ]
        );
    }

}
