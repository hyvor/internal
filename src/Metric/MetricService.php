<?php

namespace Hyvor\Internal\Metric;

use Hyvor\Internal\InternalApi\ComponentType;
use Prometheus\CollectorRegistry;
use Prometheus\Counter;
use Prometheus\Histogram;

class MetricService
{

    private const string NAMESPACE = 'app_api';

    private string $component;
    private Counter $requestsTotal;
    private Histogram $requestDuration;

    public function __construct(public CollectorRegistry $registry)
    {
        $this->component = ComponentType::current()->value;

        $this->requestsTotal = $registry->getOrRegisterCounter(
            self::NAMESPACE,
            'http_requests_total',
            'Total number of HTTP requests',
            ['component', 'method', 'endpoint', 'status']
        );

        $this->requestDuration = $registry->getOrRegisterHistogram(
            self::NAMESPACE,
            'http_request_duration_seconds',
            'HTTP request duration in seconds',
            ['component', 'method', 'endpoint', 'status'],
            [0.1, 0.25, 0.5, 1, 2.5, 5]
        );
    }

    public function newRequest(
        string $method,
        string $endpoint,
        int $status,
        float $duration
    ): void {
        $this->requestsTotal->inc(
            [
                $this->component,
                $method,
                $endpoint,
                $status,
            ]
        );

        $this->requestDuration->observe(
            $duration,
            [
                $this->component,
                $method,
                $endpoint,
                $status,
            ]
        );
    }

    public function getSamples(): mixed
    {
        return $this->registry->getMetricFamilySamples();
    }

}