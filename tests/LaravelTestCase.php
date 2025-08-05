<?php

namespace Hyvor\Internal\Tests;

use Illuminate\Container\Container;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class LaravelTestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        $composer = json_decode((string)file_get_contents(__DIR__ . '/../composer.json'), true);
        $providers = $composer['extra']['laravel']['providers'] ?? [];
        return $providers;
    }

    protected function setUp(): void
    {
        parent::setUp();
        app()->singleton(HttpClientInterface::class, fn() => new MockHttpClient());
    }

    protected function getContainer(): Container
    {
        return app();
    }

    /**
     * @param MockResponse|MockResponse[] $response
     */
    protected function setHttpClientResponse(MockResponse|array $response): void
    {
        $client = new MockHttpClient($response);
        app()->singleton(HttpClientInterface::class, fn() => $client);
    }

}
