<?php

namespace Hyvor\Internal\Metric;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Prometheus\RenderTextFormat;

class MetricMiddleware
{

    public function __construct(private MetricService $metricService)
    {
    }

    public function handle(Request $request, \Closure $next): mixed
    {
        $start = microtime(true);

        $isMetricsEndpoint = $this->isMetricsEndpoint($request);
        if ($isMetricsEndpoint) {
            $response = $this->getMetricsResponse();
        } else {
            $response = $next($request);
        }
        $duration = microtime(true) - $start;

        $endpoint = $isMetricsEndpoint ? '<metrics>' : $this->getEndpoint($request);
        $this->metricService->newRequest(
            $request->method(),
            $endpoint,
            $response->getStatusCode(),
            $duration
        );

        return $response;
    }

    private function isMetricsEndpoint(Request $request): bool
    {
        // In local, we use the endpoint /api/metrics
        if (config('app.env') === 'local') {
            return $request->is('api/metrics');
        }

        /**
         * In non-local, the HYVOR_METRICS_SERVER env variable should be set
         * Caddyfile config will look like this:
         * :9667 {
         *      php_server {
         *          root * /app/backend/public
         *          file_server off
         *          env HYVOR_METRICS_SERVER 1
         *          try_files frankenphp-worker.php
         *      }
         * }
         */
        return boolval($_SERVER['HYVOR_METRICS_SERVER'] ?? false);
    }

    private function getEndpoint(Request $request): string
    {
        /**
         * See hyvor/monitoring/API.md
         * This returns placeholder /api/sudo/user/{id} for /api/sudo/user/123
         */
        $route = $request->route();
        return $route instanceof Route ? '/' . $route->uri() : '/<unknown>';
    }

    private function getMetricsResponse(): mixed
    {
        $renderer = new RenderTextFormat();
        return response($renderer->render($this->metricService->getSamples()))
            ->header('Content-Type', RenderTextFormat::MIME_TYPE);
    }

}