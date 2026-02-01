<?php

namespace Hyvor\Internal\Billing;

use Hyvor\Internal\Billing\License\License;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicenseType;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\InternalConfig;
use Symfony\Component\DependencyInjection\Container;

class BillingFake implements BillingInterface
{

    /**
     * @param License|(callable(int $organizationId, ?int $blogId, Component $component) : ?License)|null $license
     * @param array<int, ResolvedLicense>|(callable(int[] $organizationIds, Component $component) : array<int, ResolvedLicense>)|null $licenses
     * @return void
     */
    public static function enable(
        null|License|callable $license = null,
        null|array|callable $licenses = null
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
     * @param License|(callable(int $organizationId, ?int $blogId, Component $component) : ?License)|null $license
     * @param array<int, ResolvedLicense>|(callable(int[] $organizationIds, Component $component) : array<int, ResolvedLicense>)|null $licenses
     * @return void
     */
    public static function enableForSymfony(
        Container $container,
        null|License|callable $license = null,
        null|array|callable $licenses = null
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
         * @param ResolvedLicense|(callable(int $organizationId, Component $component) : ?ResolvedLicense)|null $license
         */
        private readonly mixed $license = null,

        /**
         * @param array<int, ResolvedLicense>|(callable(int[] $organizationIds, Component $component) : array<int, ResolvedLicense>)|null $licenses
         */
        private readonly mixed $licenses = null
    ) {
    }

    public function license(int $organizationId, ?Component $component = null): ResolvedLicense
    {
        $component ??= $this->internalConfig->getComponent();

        if ($this->license === null) {
            return new ResolvedLicense(ResolvedLicenseType::NONE);
        }

        if ($this->license instanceof ResolvedLicense) {
            return $this->license;
        }

        return ($this->license)($organizationId, $component);
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
