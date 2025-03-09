<?php

namespace Hyvor\Internal\Tests\Unit\InternalApi;

use Hyvor\Internal\Component\Component;
use Hyvor\Internal\InternalApi\Exceptions\InternalApiCallFailedException;
use Hyvor\Internal\InternalApi\Exceptions\InvalidMessageException;
use Hyvor\Internal\InternalApi\InternalApi;
use Hyvor\Internal\InternalApi\InternalApiMethod;
use Hyvor\Internal\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(InternalApi::class)]
class InternalApiTest extends TestCase
{
    public function testCallsTalkInternalApi(): void
    {
        $this->freezeTime();

        Http::fake([
            'talk.hyvor.com/api/internal/delete-user' => Http::response(['success' => true], 200)
        ]);

        InternalApi::call(
            Component::TALK,
            'POST',
            'delete-user',
            ['user_id' => 123]
        );

        Http::assertSent(function ($request) {
            $this->assertEquals('https://talk.hyvor.com/api/internal/delete-user', $request->url());

            $message = $request['message'];
            $message = decrypt($message, false);
            $data = json_decode($message, true);

            $this->assertEquals(123, $data['data']['user_id']);
            $this->assertEquals(now()->timestamp, $data['timestamp']);

            $headers = $request->headers();
            $this->assertEquals('core', $headers['X-Internal-Api-From'][0]);
            $this->assertEquals('talk', $headers['X-Internal-Api-To'][0]);

            return true;
        });
    }

    public function testCallsWithGet(): void
    {
        Http::fake([
            'talk.hyvor.com/api/internal/sudo/users*' => Http::response(['success' => true], 200)
        ]);

        $response = InternalApi::call(
            Component::TALK,
            InternalApiMethod::GET,
            '/sudo/users',
            ['user_id' => 123]
        );

        $this->assertEquals(['success' => true], $response);

        Http::assertSent(function ($request) {
            $this->assertStringStartsWith('https://talk.hyvor.com/api/internal/sudo/users', $request->url());
            $this->assertEquals('GET', $request->method());

            $message = $request['message'];
            $message = decrypt($message, false);
            $data = json_decode($message, true);
            $this->assertEquals(123, $data['data']['user_id']);

            return true;
        });
    }

    public function testThrowsAnErrorIfTheResponseFails(): void
    {
        Http::fake([
            'talk.hyvor.com/api/internal/delete-user' => Http::response(['success' => false], 500)
        ]);

        $this->expectException(InternalApiCallFailedException::class);
        $this->expectExceptionMessage(
            'Internal API call to https://talk.hyvor.com/api/internal/delete-user failed. Status code: 500 - {"success":false}'
        );

        InternalApi::call(
            Component::TALK,
            'POST',
            'delete-user',
            ['user_id' => 123]
        );
    }

    public function testThrowsAnErrorOnConnectionException(): void
    {
        Http::fake([
            'talk.hyvor.com/api/internal/delete-user' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection error');
            }
        ]);

        $this->expectException(InternalApiCallFailedException::class);
        $this->expectExceptionMessage(
            'Internal API call to https://talk.hyvor.com/api/internal/delete-user failed. Connection error: Connection error'
        );

        InternalApi::call(
            Component::TALK,
            'POST',
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

        $message = InternalApi::messageFromData($data);

        $this->assertEquals(
            [
                'data' => $data,
                'timestamp' => now()->timestamp,
            ],
            json_decode(decrypt($message, false), true)
        );
    }

    // START: dataFromMessage

    /**
     * @param array<string, mixed>|mixed $data
     * @return string
     */
    private function getEncryptedMessage(mixed $data, mixed $timestamp = null): string
    {
        return Crypt::encryptString(
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

        $this->assertEquals($data, InternalApi::dataFromMessage($message));
    }

    public function testDataFromMessageInvalidEncryption(): void
    {
        $this->expectException(InvalidMessageException::class);
        $this->expectExceptionMessage('Failed to decrypt message');

        InternalApi::dataFromMessage('invalid');
    }

    public function testDataFromMessageInvalidData(): void
    {
        $this->expectException(InvalidMessageException::class);
        $this->expectExceptionMessage('Invalid data');

        InternalApi::dataFromMessage(Crypt::encryptString('invalid'));
    }

    public function testDataFromMessageInvalidTimestamp(): void
    {
        $data = [];
        $message = $this->getEncryptedMessage($data, 'invalid');

        $this->expectException(InvalidMessageException::class);
        $this->expectExceptionMessage('Invalid timestamp');

        InternalApi::dataFromMessage($message);
    }

    public function testDataFromMessageExpiredTimestamp(): void
    {
        $data = [];
        $message = $this->getEncryptedMessage($data, now()->subMinutes(61)->timestamp);

        $this->expectException(InvalidMessageException::class);
        $this->expectExceptionMessage('Expired message');

        InternalApi::dataFromMessage($message);
    }

    public function testDataFromMessageDataIsNotArray(): void
    {
        $message = $this->getEncryptedMessage('invalid');

        $this->expectException(InvalidMessageException::class);
        $this->expectExceptionMessage('Data is not an array');

        InternalApi::dataFromMessage($message);
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
