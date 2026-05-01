<?php

namespace Hyvor\Internal\Billing\License\Plan;

use Hyvor\Internal\Billing\License\License;

/**
 * @template T of License = License
 */
abstract class PlanAbstract
{

    /**
     * @var array<int, array<string, Plan<T>>>
     */
    private array $versions;

    /**
     * @var array<string, Meter>
     */
    private array $meters = [];

    // this only helps with config() method. Do not use for anything else.
    private ?int $currentVersionForConfig = null;

    public function __construct()
    {
        $this->config();
    }

    /**
     * Configure the plans here
     */
    abstract protected function config(): void;

    // only for configuration
    protected function version(int $version, callable $callback): void
    {
        $this->currentVersionForConfig = $version;
        $this->versions[$version] = [];
        $callback();
        $this->currentVersionForConfig = null;
    }

    // only for configuration

    /**
     * @param T $license
     */
    protected function plan(
        string $name,
        float $monthlyPrice,
        License $license,
        ?string $nameReadable = null,
        ?string $group = null,
        ?string $meterName = null,
    ): void {
        assert($this->currentVersionForConfig !== null);

        $meter = $meterName ? $this->meters[$meterName] : null;
        if ($meter) {
            assert(property_exists($license, $meter->property));
        }

        $plan = new Plan(
            $this->currentVersionForConfig,
            $name,
            $monthlyPrice,
            $license,
            $nameReadable,
            $group,
            $meter,
        );

        $currentVersionPlans = $this->versions[$this->currentVersionForConfig];
        $currentVersionPlans[$name] = $plan;

        $this->versions[$this->currentVersionForConfig] = $currentVersionPlans;
    }

    protected function meter(
        string $name,
        string $nameReadable,
        string $property,
        float $pricePerUnit,
    ): void {
        if (isset($this->meters[$name])) {
            throw new \Exception("Meter with name $name already exists");
        }

        $this->meters[$name] = new Meter(
            name: $name,
            property: $property,
            nameReadable: $nameReadable,
            pricePerUnit: $pricePerUnit,
        );
    }

    /**
     * @return array<int, array<string, Plan<T>>>
     */
    public function getAll(): array
    {
        return $this->versions;
    }

    public function getCurrentVersion(): int
    {
        return (int)array_key_last($this->versions);
    }

    /**
     * @return array<string, Plan<T>>
     */
    public function getCurrentPlans(): array
    {
        $version = array_key_last($this->versions);
        assert(is_int($version));
        return $this->versions[$version];
    }

    /**
     * @return Plan<T>
     */
    public function getPlan(string $name, ?int $version = null): Plan
    {
        $version ??= $this->getCurrentVersion();
        return $this->versions[$version][$name];
    }

    /**
     * Same as getPlan() but returns null if the plan is not found.
     * @return Plan<T>|null
     */
    public function tryGetPlan(string $name, ?int $version = null): ?Plan
    {
        $version ??= $this->getCurrentVersion();
        return $this->versions[$version][$name] ?? null;
    }

    /**
     * @return array<string, Meter>
     */
    public function getMeters(): array
    {
        return $this->meters;
    }
}
