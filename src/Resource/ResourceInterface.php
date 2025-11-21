<?php

namespace Hyvor\Internal\Resource;

use Carbon\Carbon;

interface ResourceInterface
{

    public function register(int $organizationId, int $resourceId, ?Carbon $at = null): void;

    public function delete(int $resourceId): void;

}