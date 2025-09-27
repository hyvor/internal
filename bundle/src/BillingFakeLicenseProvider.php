<?php

namespace Hyvor\Internal\Bundle;

use Hyvor\Internal\Component\Component;
use Hyvor\Internal\InternalFake;

class BillingFakeLicenseProvider
{

    private InternalFake $internalFake;

    /**
     * @param class-string<InternalFake> $internalFakeClass
     */
    public function __construct(string $internalFakeClass)
    {
        $this->internalFake = new $internalFakeClass();
    }

    public function license(int $userId, ?int $blogId, Component $component)
    {
        return $this->internalFake->license($userId, $blogId, $component);
    }

    public function licenses(array $of, Component $component)
    {
        return $this->internalFake->licenses($of, $component);
    }

}
