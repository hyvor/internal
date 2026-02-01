<?php

namespace Hyvor\Internal\Billing;

use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\InternalConfig;
use Symfony\Component\DependencyInjection\Container;

class BillingFake implements BillingInterface
{

    /**
     * @param array<int, ResolvedLicense>|(callable(int[] $organizationIds, Component $component) : array<int, ResolvedLicense>) $licenses
     * @return void
     */
    public static function enable(array|callable $licenses): void {
        app()->singleton(Billing::class, function () use ($licenses) {
            return new BillingFake(
                app(InternalConfig::class),
                $licenses
            );
        });
    }

    /**
     * @param array<int, ResolvedLicense>|(callable(int[] $organizationIds, Component $component) : array<int, ResolvedLicense>) $licenses
     * @return void
     */
    public static function enableForSymfony(
        Container $container,
        array|callable $licenses
    ): void {
        $internalConfig = $container->get(InternalConfig::class);
        assert($internalConfig instanceof InternalConfig);

        $fake = new self(
            $internalConfig,
            $licenses
        );
        $container->set(BillingInterface::class, $fake);
    }

    public function __construct(
        private InternalConfig $internalConfig,

        /**
         * @param array<int, ResolvedLicense>|(callable(int[] $organizationIds, Component $component) : array<int, ResolvedLicense>)|null $licenses
         */
        private readonly mixed $licenses = null
    ) {
    }

    public function license(int $organizationId, ?Component $component = null): ResolvedLicense
    {
        $license = $this->licenses([$organizationId], $component)[$organizationId] ?? null;

        if (!$license) {
            throw new \Exception("No license found for organization ID {$organizationId} in BillingFake");
        }

        return $license;
    }

    /**
     * @param int[] $organizationIds
     * @return array<int, ResolvedLicense>
     */
    public function licenses(array $organizationIds, ?Component $component = null): array
    {
        $component ??= $this->internalConfig->getComponent();

        if ($this->licenses === null) {
            // @codeCoverageIgnoreStart
            throw new \Exception('No licenses set in BillingFake::enable()');
            // @codeCoverageIgnoreEnd
        }

        if (is_array($this->licenses)) {
            return $this->licenses;
        }

        return ($this->licenses)($organizationIds, $component);
    }

}
