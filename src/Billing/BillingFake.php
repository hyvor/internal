<?php

namespace Hyvor\Internal\Billing;

use Hyvor\Internal\Billing\Dto\LicenseOf;
use Hyvor\Internal\Billing\Dto\LicensesCollection;
use Hyvor\Internal\Billing\License\License;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\InternalConfig;

class BillingFake implements BillingInterface
{

    /**
     * @param License|(callable(int $userId, ?int $blogId, Component $component) : ?License)|null $license
     * @param LicensesCollection|(callable(array<LicenseOf> $of, Component $component): LicensesCollection)|null $licenses
     * @return void
     */
    public static function enable(
        null|License|callable $license = null,
        null|LicensesCollection $licenses = null
    ): void {
        app()->singleton(Billing::class, function () use ($license) {
            return new BillingFake(app(InternalConfig::class), $license);
        });
    }

    public function __construct(
        private InternalConfig $internalConfig,

        /**
         * @param License|(callable(int $userId, ?int $resouceId, Component $component) : ?License)|null $license
         */
        private readonly mixed $license = null,
    ) {
    }

    public function license(int $userId, ?int $resourceId, ?Component $component = null): ?License
    {
        $component ??= $this->internalConfig->getComponent();

        if ($this->license === null) {
            return null;
        }

        if ($this->license instanceof License) {
            return $this->license;
        }

        return ($this->license)($userId, $resourceId, $component);
    }

    public function licenses(array $of, ?Component $component = null): LicensesCollection
    {
        // TODO: Implement licenses() method.
    }
}
