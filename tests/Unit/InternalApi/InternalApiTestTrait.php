<?php

namespace Hyvor\Internal\Tests\Unit\InternalApi;

use Hyvor\Internal\Component\Component;
use Hyvor\Internal\InternalApi\Exceptions\InternalApiCallFailedException;
use Hyvor\Internal\InternalApi\Exceptions\InvalidMessageException;
use Hyvor\Internal\InternalApi\InternalApi;
use Hyvor\Internal\Util\Crypt\Encryption;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

trait InternalApiTestTrait
{

    private function getInternalApi(): InternalApi
    {
        /** @var InternalApi $internalApi */
        $internalApi = $this->getContainer()->get(InternalApi::class);
        return $internalApi;
    }

    private function getEncryption(): Encryption
    {
        /** @var Encryption $encryption */
        $encryption = $this->getContainer()->get(Encryption::class);
        return $encryption;
    }

    private function setResponseFactory(MockResponse $response): void
    {
        $this->setHttpClientResponse($response);
    }

    public function testCallsTalkInternalApi(): void
    {
        $response = new JsonMockResponse(['success' => true]);
        $this->setResponseFactory($response);

        $internalApi = $this->getInternalApi();
        $internalApi->call(
            Component::TALK,
            'delete-user',
            ['user_id' => 123]
        );

        $this->assertSame('https://talk.hyvor.internal/api/internal/delete-user', $response->getRequestUrl());

        $data = $internalApi->dataFromMockResponse($response);
        $this->assertSame(123, $data['user_id']);

        $headers = $response->getRequestOptions()['headers'];
        $this->assertContains('X-Internal-Api-From: core', $headers);
        $this->assertContains('X-Internal-Api-To: talk', $headers);
    }

    public function testThrowsAnErrorIfTheResponseFails(): void
    {
        $response = new JsonMockResponse(['success' => false], [
            'http_code' => 500,
        ]);
        $this->setResponseFactory($response);

        $this->expectException(InternalApiCallFailedException::class);
        $this->expectExceptionMessage(
            'Internal API call to https://talk.hyvor.internal/api/internal/delete-user failed. Status code: 500 - {"success":false}'
        );

        $internalApi = $this->getInternalApi();
        $internalApi->call(
            Component::TALK,
            'delete-user',
            ['user_id' => 123]
        );
    }

    public function testThrowsAnErrorOnConnectionException(): void
    {
        $response = new MockResponse(info: ['error' => 'host does not exist']);
        $this->setResponseFactory($response);

        $this->expectException(InternalApiCallFailedException::class);
        $this->expectExceptionMessage(
            'Internal API call to https://talk.hyvor.internal/api/internal/delete-user failed. Connection error: host does not exist'
        );

        $internalApi = $this->getInternalApi();
        $internalApi->call(
            Component::TALK,
            'delete-user',
            ['user_id' => 123]
        );
    }

    public function testThrowsAnErrorOnDecodingException(): void
    {
        $response = new MockResponse('[invalidjson]');
        $this->setResponseFactory($response);

        $this->expectException(InternalApiCallFailedException::class);
        $this->expectExceptionMessage(
            'Internal API call to https://talk.hyvor.internal/api/internal/delete-user failed. Decoding error:'
        );

        $internalApi = $this->getInternalApi();
        $internalApi->call(
            Component::TALK,
            'delete-user',
            ['user_id' => 123]
        );
    }

    // ==================== Helper functions ====================

    public function testMessageFromData(): void
    {
        $data = [
            'user_id' => 123,
            'name' => 'John Doe',
        ];

        $internalApi = $this->getInternalApi();
        $message = $internalApi->messageFromData($data);

        $this->assertEquals(
            [
                'data' => $data,
                'timestamp' => now()->timestamp,
            ],
            json_decode($this->getEncryption()->decryptString($message), true)
        );
    }

    // START: dataFromMessage

    /**
     * @param array<string, mixed>|mixed $data
     * @return string
     */
    private function getEncryptedMessage(mixed $data, mixed $timestamp = null): string
    {
        $encryption = $this->getEncryption();
        return $encryption->encryptString(
            (string)json_encode([
                'data' => $data,
                'timestamp' => $timestamp ?? now()->timestamp,
            ])
        );
    }

    public function testDataFromMessage(): void
    {
        $data = [
            'user_id' => 123,
            'name' => 'John Doe',
        ];
        $message = $this->getEncryptedMessage($data);

        $this->assertEquals($data, $this->getInternalApi()->dataFromMessage($message));
    }

    public function testDataFromMessageInvalidEncryption(): void
    {
        $this->expectException(InvalidMessageException::class);
        $this->expectExceptionMessage('Failed to decrypt message');

        $this->getInternalApi()->dataFromMessage('invalid');
    }

    public function testDataFromMessageInvalidData(): void
    {
        $this->expectException(InvalidMessageException::class);
        $this->expectExceptionMessage('Invalid data');
        $this->getInternalApi()->dataFromMessage($this->getEncryption()->encryptString('invalid'));
    }

    public function testDataFromMessageInvalidTimestamp(): void
    {
        $data = [];
        $message = $this->getEncryptedMessage($data, 'invalid');

        $this->expectException(InvalidMessageException::class);
        $this->expectExceptionMessage('Invalid timestamp');

        $this->getInternalApi()->dataFromMessage($message);
    }

    public function testDataFromMessageExpiredTimestamp(): void
    {
        $data = [];
        $message = $this->getEncryptedMessage($data, now()->subMinutes(61)->timestamp);

        $this->expectException(InvalidMessageException::class);
        $this->expectExceptionMessage('Expired message');

        $this->getInternalApi()->dataFromMessage($message);
    }

    public function testDataFromMessageDataIsNotArray(): void
    {
        $message = $this->getEncryptedMessage('invalid');

        $this->expectException(InvalidMessageException::class);
        $this->expectExceptionMessage('Data is not an array');

        $this->getInternalApi()->dataFromMessage($message);
    }

    // END: dataFromMessage

    public function testRequestingComponent(): void
    {
        $request = new Request(
            server: [
                'HTTP_X-Internal-Api-From' => 'talk',
            ]
        );

        $this->assertEquals(Component::TALK, InternalApi::getRequestingComponent($request));
    }

}