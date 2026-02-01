<?php

namespace Hyvor\Internal\Bundle\Testing;

use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\HttpFoundation\Response;

trait ApiTestingTrait
{

    use BaseTestingTrait;

    /**
     * @return array<mixed>
     */
    public function getJson(): array
    {
        /** @var AbstractBrowser<object, \Symfony\Component\BrowserKit\Response> $client */
        $client = self::getClient();
        $response = $client->getResponse();
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);
        $json = json_decode($content, true);
        $this->assertIsArray($json);
        return $json;
    }

    /**
     * @deprecated use assertResponseFailed
     */
    public function assertFailed(int $code, ?string $message = null): void
    {
        $this->assertResponseFailed($code, $message);
    }

    public function assertResponseFailed(int $code, ?string $message = null): void
    {
        /** @var AbstractBrowser<object, \Symfony\Component\BrowserKit\Response> $client */
        $client = self::getClient();
        $response = $client->getResponse();
        $this->assertSame($code, $response->getStatusCode());

        if ($message !== null) {
            $error = $this->getJson()['message'] ?? '';
            $this->assertStringContainsString($message, $error);
        }
    }

    public function assertViolationCount(int $count): void
    {
        /** @var AbstractBrowser<object, \Symfony\Component\BrowserKit\Response> $client */
        $client = self::getClient();
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $response = $this->getJson();

        $this->assertArrayHasKey('violations', $response);
        $this->assertIsArray($response['violations']);
        $this->assertCount($count, $response['violations']);
    }

    public function assertHasViolation(string $property, string $message = ''): void
    {
        /** @var AbstractBrowser<object, \Symfony\Component\BrowserKit\Response> $client */
        $client = self::getClient();
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $response = $this->getJson();

        $this->assertArrayHasKey('violations', $response);
        $this->assertIsArray($response['violations']);

        $found = false;
        foreach ($response['violations'] as $violation) {
            $this->assertIsArray($violation);
            if ($violation['property'] === $property) {
                $found = true;
                if ($message) {
                    $this->assertStringContainsString($message, $violation['message']);
                }
            }
        }

        $this->assertTrue($found, 'Violation not found');
    }

}
