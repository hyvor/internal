<?php

namespace Hyvor\Internal\Tests;

class LaravelTestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        $composer = json_decode((string)file_get_contents(__DIR__ . '/../composer.json'), true);
        $providers = $composer['extra']['laravel']['providers'] ?? [];
        return $providers;
    }

}
