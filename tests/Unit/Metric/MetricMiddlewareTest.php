<?php

namespace Hyvor\Internal\Tests\Unit\Metric;

use Hyvor\Internal\Metric\MetricMiddleware;
use Hyvor\Internal\Metric\MetricService;
use Hyvor\Internal\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Prometheus\MetricFamilySamples;

class MetricMiddlewareTest extends TestCase
{

    /**
     * @param array<MetricFamilySamples> $metrics
     */
    private function findMetric(array $metrics, string $name): MetricFamilySamples
    {
        foreach ($metrics as $metric) {
            if ($metric->getName() === $name) {
                return $metric;
            }
        }

        $this->fail("Metric $name not found");
    }

    public function test_increases(): void
    {
        /** @var MetricService $service */
        $service = app()->make(MetricService::class);
        $middleware = new MetricMiddleware($service);

        $request = new Request(server: ['REQUEST_URI' => '/test']);
        $request->setRouteResolver(function () use ($request) {
            return (new Route('GET', '/test', []))->bind($request);
        });
        $middleware->handle($request, function () {
            return response('OK');
        });

        $metrics = $service->registry->getMetricFamilySamples();

        // total, duration, php_info
        $this->assertCount(3, $metrics);

        $total = $this->findMetric($metrics, 'app_api_http_requests_total');
        $this->assertSame('counter', $total->getType());
        $this->assertSame('component', $total->getLabelNames()[0]);
        $this->assertSame('method', $total->getLabelNames()[1]);
        $this->assertSame('endpoint', $total->getLabelNames()[2]);
        $this->assertSame('status', $total->getLabelNames()[3]);

        $totalSample = $total->getSamples()[0];
        $this->assertSame("1", $totalSample->getValue());
        $this->assertSame([
            'core',
            'GET',
            '/test',
            '200',
        ], $totalSample->getLabelValues());

        $duration = $this->findMetric($metrics, 'app_api_http_request_duration_seconds');
        $this->assertSame('histogram', $duration->getType());

        $this->assertSame('component', $duration->getLabelNames()[0]);
        $this->assertSame('method', $duration->getLabelNames()[1]);
        $this->assertSame('endpoint', $duration->getLabelNames()[2]);
        $this->assertSame('status', $duration->getLabelNames()[3]);

        $durationSamples = $duration->getSamples();
        $this->assertCount(9, $durationSamples);
    }

    public function test_metrics_endpoint_on_local(): void
    {
        config(['app.env' => 'local']);

        /** @var MetricService $service */
        $service = app()->make(MetricService::class);
        $middleware = new MetricMiddleware($service);

        $service->newRequest('GET', '/api/metrics', 200, 0.1);

        $request = new Request(server: ['REQUEST_URI' => '/api/metrics']);
        $request->setRouteResolver(function () use ($request) {
            return (new Route('GET', '/api/metrics', []))->bind($request);
        });
        $response = $middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertSame('text/plain; version=0.0.4', $response->headers->get('Content-Type'));

        $body = $response->getContent();
        $this->assertStringContainsString(
            'app_api_http_requests_total{component="core",method="GET",endpoint="/api/metrics",status="200"} 1',
            $body
        );
    }

    public function test_metrics_not_on_production(): void
    {
        config(['app.env' => 'production']);

        /** @var MetricService $service */
        $service = app()->make(MetricService::class);
        $middleware = new MetricMiddleware($service);

        $request = new Request(server: ['REQUEST_URI' => '/api/metrics']);
        $request->setRouteResolver(function () use ($request) {
            return (new Route('GET', '/api/metrics', []))->bind($request);
        });
        $response = $middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertSame('OK', $response->getContent());
    }

    public function test_metrics_with_env_variable(): void
    {
        config(['app.env' => 'production']);
        $_ENV['HYVOR_METRICS_SERVER'] = '1';

        /** @var MetricService $service */
        $service = app()->make(MetricService::class);
        $middleware = new MetricMiddleware($service);

        $request = new Request(server: ['REQUEST_URI' => '/api/metrics']);
        $request->setRouteResolver(function () use ($request) {
            return (new Route('GET', '/api/metrics', []))->bind($request);
        });
        $response = $middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertSame('text/plain; version=0.0.4', $response->headers->get('Content-Type'));
    }

}