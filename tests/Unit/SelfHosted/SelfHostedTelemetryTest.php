<?php

namespace Hyvor\Internal\Tests\Unit\SelfHosted;

use Hyvor\Internal\SelfHosted\Provider\TelemetryProviderInterface;
use Hyvor\Internal\SelfHosted\SelfHostedTelemetry;
use Hyvor\Internal\SelfHosted\SelfHostedTelemetryInterface;
use Hyvor\Internal\Tests\Helper\UpdatesInternalConfig;
use Hyvor\Internal\Tests\SymfonyTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(SelfHostedTelemetry::class)]
class SelfHostedTelemetryTest extends SymfonyTestCase
{

    use UpdatesInternalConfig;

    private function getTelemetryService(): SelfHostedTelemetryInterface
    {
        /** @var SelfHostedTelemetryInterface $service */
        $service = $this->container->get(SelfHostedTelemetryInterface::class);
        return $service;
    }

    public function test_does_not_allow_non_selfhostable(): void
    {
        $this->updateInternalConfig('component', 'post');

        $this->expectException(\AssertionError::class);
        $this->expectExceptionMessage('Component "post" is not self-hostable');

        $telemetry = $this->getTelemetryService();
        $telemetry->recordTelemetry();
    }

    protected function getEnv(): string
    {
        if (str_ends_with($this->name(), '_on_env_test')) {
            return 'test';
        }
        if (str_ends_with($this->name(), '_on_env_dev')) {
            return 'dev';
        }
        return 'prod';
    }

    public function test_fails_on_env_test(): void
    {
        $this->updateInternalConfig('component', 'relay');
        $this->expectException(\AssertionError::class);
        $this->expectExceptionMessage('Mock SelfHostedTelemetryInterface in tests');

        $telemetry = $this->getTelemetryService();
        $telemetry->recordTelemetry();
    }

    public function test_hyvorcom_not_allowed_on_env_dev(): void
    {
        $this->updateInternalConfig('component', 'relay');
        $this->updateInternalConfig('privateInstance', 'https://hyvor.com');

        $this->expectException(\AssertionError::class);
        $this->expectExceptionMessage(
            'DEV environment should not send telemetry to hyvor.com.'
        );

        $telemetry = $this->getTelemetryService();
        $telemetry->recordTelemetry();
    }

    public function test_records_telemetry(): void
    {
        $this->updateInternalConfig('component', 'relay');
        $this->updateInternalConfig('privateInstance', 'https://hyvor.com');

        $telemetryProvider = $this->createMock(TelemetryProviderInterface::class);
        $telemetryProvider->method('getInstanceUuid')->willReturn('test-uuid');
        $telemetryProvider->method('getVersion')->willReturn('1.0.0');
        $telemetryProvider->method('getPayload')->willReturn(['key' => 'value']);
        $this->container->set(TelemetryProviderInterface::class, $telemetryProvider);

        $response = new JsonMockResponse();
        $this->container->set(HttpClientInterface::class, new MockHttpClient($response));

        $telemetry = $this->getTelemetryService();
        $telemetry->recordTelemetry();

        $this->assertSame('https://hyvor.com/api/public/self-hosted/telemetry', $response->getRequestUrl());
        $this->assertSame('POST', $response->getRequestMethod());

        $body = $response->getRequestOptions()['body'];
        $json = json_decode($body, true);

        $this->assertSame([
            'instance_uuid' => 'test-uuid',
            'product' => 'relay',
            'version' => '1.0.0',
            'payload' => ['key' => 'value'],
        ], $json);
    }

}