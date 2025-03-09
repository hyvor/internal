<?php

namespace Hyvor\Internal\Billing;

use Hyvor\Internal\Billing\License\License;
use Hyvor\Internal\Component\Component;

class BillingFake extends Billing
{

    /**
     * @param License|(callable(int $userId, ?int $blogId, Component $component) : ?License)|null $license
     * @return void
     */
    public static function enable(
        null|License|callable $license = null,
    ): void {
        app()->singleton(Billing::class, function () use ($license) {
            return new BillingFake($license);
        });
    }

    public function __construct(
        /**
         * @param License|(callable(int $userId, ?int $resouceId, Component $component) : ?License)|null $license
         */
        private readonly mixed $license = null
    ) {
    }

    public function license(int $userId, ?int $resourceId, ?Component $component = null): ?License
    {
        $component ??= Component::current();

        if ($this->license === null) {
            return null;
        }

        if ($this->license instanceof License) {
            return $this->license;
        }

        return ($this->license)($userId, $resourceId, $component);
    }

}
