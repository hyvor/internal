<?php

namespace Hyvor\Internal\SelfHosted;

use Hyvor\Internal\Component\Component;
use Hyvor\Internal\Component\InstanceUrlResolver;
use Hyvor\Internal\InternalConfig;
use Hyvor\Internal\SelfHosted\Provider\TelemetryProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

// Symfony-only
class SelfHostedTelemetry implements SelfHostedTelemetryInterface
{

    public function __construct(
        private InternalConfig $internalConfig,
        private TelemetryProviderInterface $telemetryProvider,
        private InstanceUrlResolver $instanceUrlResolver,
        private HttpClientInterface $client,
        #[Autowire('%kernel.environment%')]
        private string $env,
    ) {
    }

    /**
     * Call this method once every 24 hours to record telemetry data.
     * Use a scheduled job that runs every 24 hours.
     * Do not run all at 00:00 UTC, instead randomize the time (based on UUID) to avoid spikes.
     * @throws ExceptionInterface
     */
    public function recordTelemetry(): void
    {
        $component = $this->internalConfig->getComponent();

        assert($component->selfHostable(), "Component {$component->value} is not self-hostable");
        assert($this->env !== 'test', 'Mock SelfHostedTelemetryInterface in tests');

        // TODO: relying on instanceUrlResolver is not ideal here
        // as HYVOR_INSTANCE has nothing to do with self-hosting
        // but this works since the default URL is https://hyvor.com
        // also, this disables telemetry on DEV
        $componentUrl = $this->instanceUrlResolver->privateUrlOfCore();

        assert(
            $this->env !== 'dev' || $componentUrl !== 'https://hyvor.com',
            'DEV environment should not send telemetry to hyvor.com. Set HYVOR_INSTANCE to a different URL.',
        );

        $url = $componentUrl . '/api/public/self-hosted/telemetry';

        $this->telemetryProvider->record();

        $data = [
            'instance_uuid' => $this->telemetryProvider->getInstanceUuid(),
            'product' => $component->value,
            'version' => $this->telemetryProvider->getVersion(),
            'payload' => $this->telemetryProvider->getPayload(),
        ];

        $response = $this->client->request(
            'POST',
            $url,
            [
                'json' => $data,
            ]
        );

        // to force exceptions on status codes 4xx/5xx
        $response->toArray();
    }

}