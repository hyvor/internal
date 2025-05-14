<?php

namespace Hyvor\Internal\Metric;

use Prometheus\CollectorRegistry;
use Prometheus\Counter;
use Prometheus\Histogram;

class MetricService
{

    private const string NAMESPACE = 'app_api';

    private Counter $requestsTotal;
    private Histogram $requestDuration;

    public function __construct(
        public CollectorRegistry $registry
    ) {
        $this->requestsTotal = $registry->getOrRegisterCounter(
            self::NAMESPACE,
            'http_requests_total',
            'Total number of HTTP requests',
            ['method', 'endpoint', 'status']
        );

        $this->requestDuration = $registry->getOrRegisterHistogram(
            self::NAMESPACE,
            'http_request_duration_seconds',
            'HTTP request duration in seconds',
            ['method', 'endpoint', 'status'],
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
                $method,
                $endpoint,
                (string)$status,
            ]
        );

        $this->requestDuration->observe(
            $duration,
            [
                $method,
                $endpoint,
                (string)$status,
            ]
        );
    }

    public function getSamples(): mixed
    {
        return $this->registry->getMetricFamilySamples();
    }

}