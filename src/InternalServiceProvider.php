<?php

namespace Hyvor\Internal;

use Hyvor\Internal\Auth\Auth;
use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Billing\Billing;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\BillingInterface;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\Internationalization\I18n;
use Hyvor\Internal\Metric\MetricService;
use Hyvor\Internal\Resource\ResourceFake;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\APCng;
use Prometheus\Storage\InMemory;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class InternalServiceProvider extends ServiceProvider
{

    public function boot(): void
    {
        $this->setInterfaceBindings();
        $this->config();
        $this->routes();
        $this->i18n();
        $this->metrics();
        $this->fake();
        $this->phpRuntime();
    }

    private function setInterfaceBindings(): void
    {
        $this->app->bind(HttpClientInterface::class, fn() => new CurlHttpClient());
        $this->app->singleton(AuthInterface::class, fn() => app(Auth::class));
        $this->app->singleton(BillingInterface::class, fn() => app(Billing::class));
    }

    private function config(): void
    {
        /** @var ?string $privateInstance */
        $privateInstance = config('internal.private_instance');

        $this->app->singleton(InternalConfig::class, fn() => new InternalConfig(
            str_replace('base64:', '', (string)config('app.key')),
            (string)config('internal.component'),
            (string)config('internal.instance'),
            $privateInstance,
            (bool)config('internal.fake'),
            (string)config('internal.i18n.folder'),
            config('internal.i18n.default_locale'),
        ));
    }

    private function routes(): void
    {
        // auth routes
        if (config('internal.auth.routes')) {
            $this->loadRoutesFrom(__DIR__ . '/routes/auth.php');
        }
        // testing routes
        if (App::environment('testing')) {
            $this->loadRoutesFrom(__DIR__ . '/routes/testing.php');
        }
    }

    private function i18n(): void
    {
        $this->app->singleton(I18n::class);
    }

    private function metrics(): void
    {
        $this->app->singleton(MetricService::class, fn() => new MetricService(
            new CollectorRegistry(
                apcu_enabled() ? new APCng() : new InMemory()
            )
        ));
    }

    private function fake(): void
    {
        // must be local
        if (config('app.env') !== 'local') {
            return;
        }

        // fake must be enabled in config (HYVOR_FAKE env variable)
        if (config('internal.fake') !== true) {
            return;
        }

        $class = InternalFake::class;

        if (class_exists('Hyvor\Internal\InternalFakeExtended')) {
            $class = 'Hyvor\Internal\InternalFakeExtended';
        }

        /** @var class-string<InternalFake> $class */
        $fakeConfig = new $class;

        // fake auth
        $user = $fakeConfig->user();
        $usersDatabase = $fakeConfig->usersDatabase();
        AuthFake::enable($user, $usersDatabase);

        // fake billing
        BillingFake::enable(license: function (int $userId, ?int $resourceId, Component $component) use ($fakeConfig
        ) {
            return $fakeConfig->license($userId, $resourceId, $component);
        });

        // fake resource
        ResourceFake::enable();
    }

    /**
     * PHP is not the most beautifully designed language.
     * Here, we are trying to adjust/validate some not-so-good parts of PHP.
     */
    private function phpRuntime(): void
    {
        // assert() should always throw an exception
        // docs: https://www.php.net/manual/en/function.assert.php
        if (ini_get('zend.assertions') !== '1') {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('zend.assertions must be set to 1');
            // @codeCoverageIgnoreEnd
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config.php', 'internal');
    }

}
