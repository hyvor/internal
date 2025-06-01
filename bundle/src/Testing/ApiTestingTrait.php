<?php

namespace Hyvor\Internal\Bundle\Testing;

use Symfony\Component\HttpFoundation\Response;

trait ApiTestingTrait
{

    /**
     * @return array<string, mixed>
     */
    public function getJson(): array
    {
        $response = self::getClient()->getResponse();
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);
        $json = json_decode($content, true);
        $this->assertIsArray($json);
        return $json;
    }

    public function assertViolationCount(int $count): void
    {
        $response = self::getClient()->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $response = $this->getJson();

        $this->assertArrayHasKey('violations', $response);
        $this->assertIsArray($response['violations']);
        $this->assertCount($count, $response['violations']);

        $this->assertSame(
            "Validation failed with $count violations(s)",
            $response['message'],
        );
    }

    public function assertHasViolation(string $property, string $message = ''): void
    {
        $response = self::getClient()->getResponse();
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