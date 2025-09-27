<?php

namespace Hyvor\Internal\Billing;

use Hyvor\Internal\Billing\Dto\LicenseOf;
use Hyvor\Internal\Billing\Dto\LicensesCollection;
use Hyvor\Internal\Billing\License\License;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\InternalConfig;
use Symfony\Component\DependencyInjection\Container;

class BillingFake implements BillingInterface
{

    /**
     * @param License|(callable(int $userId, ?int $blogId, Component $component) : ?License)|null $license
     * @param LicensesCollection|(callable(LicenseOf[] $of, Component $component) : LicensesCollection)|null $licenses
     * @return void
     */
    public static function enable(
        null|License|callable $license = null,
        null|LicensesCollection|callable $licenses = null
    ): void {
        app()->singleton(Billing::class, function () use ($license, $licenses) {
            return new BillingFake(
                app(InternalConfig::class),
                $license,
                $licenses
            );
        });
    }

    /**
     * @param License|(callable(int $userId, ?int $blogId, Component $component) : ?License)|null $license
     * @param LicensesCollection|(callable(LicenseOf[] $of, Component $component) : LicensesCollection)|null $licenses
     * @return void
     */
    public static function enableForSymfony(
        Container $container,
        null|License|callable $license = null,
        null|LicensesCollection|callable $licenses = null
    ): void {
        $internalConfig = $container->get(InternalConfig::class);
        assert($internalConfig instanceof InternalConfig);

        $fake = new self(
            $internalConfig,
            $license,
            $licenses
        );
        $container->set(BillingInterface::class, $fake);
    }

    public function __construct(
        private InternalConfig $internalConfig,

        /**
         * @param License|(callable(int $userId, ?int $resouceId, Component $component) : ?License)|null $license
         */
        private readonly mixed $license = null,

        /**
         * @param LicensesCollection|(callable(LicenseOf[] $of, Component $component) : LicensesCollection)|null $licenses
         */
        private readonly mixed $licenses = null
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

    /**
     * @param array<LicenseOf> $of
     */
    public function licenses(array $of, ?Component $component = null): LicensesCollection
    {
        $component ??= $this->internalConfig->getComponent();

        if ($this->licenses === null) {
            // @codeCoverageIgnoreStart
            throw new \Exception('No licenses set in BillingFake::enable()');
            // @codeCoverageIgnoreEnd
        }

        if ($this->licenses instanceof LicensesCollection) {
            return $this->licenses;
        }

        return ($this->licenses)($of, $component);
    }

}
