<?php

namespace Hyvor\Internal\Resource;

use Carbon\Carbon;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\InternalApi\Exceptions\InternalApiCallFailedException;
use Hyvor\Internal\InternalApi\InternalApi;

class Resource implements ResourceInterface
{

    public function __construct(private InternalApi $internalApi)
    {
    }

    /**
     * @throws InternalApiCallFailedException
     */
    public function register(
        int $organizationId,
        int $resourceId,
        ?Carbon $at = null
    ): void {
        $this->internalApi->call(
            Component::CORE,
            '/resource/register',
            [
                'organization_id' => $organizationId,
                'resource_id' => $resourceId,
                'at' => $at?->getTimestamp(),
            ]
        );
    }

    /**
     * @throws InternalApiCallFailedException
     */
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
