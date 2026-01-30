<?php

namespace Hyvor\Internal\InternalApi;

use Hyvor\Internal\Component\Component;
use Hyvor\Internal\Component\InstanceUrlResolver;
use Hyvor\Internal\InternalApi\Exceptions\InternalApiCallFailedException;
use Hyvor\Internal\InternalApi\Exceptions\InvalidMessageException;
use Hyvor\Internal\InternalConfig;
use Hyvor\Internal\Util\Crypt\Encryption;
use Illuminate\Contracts\Encryption\DecryptException;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Call the internal API between components
 */
class InternalApi
{

    public function __construct(
        private InternalConfig $config,
        private Encryption $encryption,
        private HttpClientInterface $client,
        private InstanceUrlResolver $instanceUrlResolver
    ) {
    }

    /**
     * @param array<mixed> $data
     * @return array<mixed>
     * @throws InternalApiCallFailedException
     */
    public function call(
        Component $to,
        /**
         * This is the part after the `/api/internal/` in the URL
         * ex: set `/delete-user` to call `/api/internal/delete-user`
         */
        string $endpoint,
        array $data = [],
        ?Component $from = null
    ): array {
        $endpoint = ltrim($endpoint, '/');
        $componentUrl = $this->instanceUrlResolver->privateUrlOf($to);

        $url = $componentUrl . '/api/internal/' . $endpoint;

        $message = $this->messageFromData($data);
        $from ??= $this->config->getComponent();

        $headers = [
            'Content-Type' => 'application/json',
            'X-Internal-Api-From' => $from->value,
            'X-Internal-Api-To' => $to->value,
        ];

        try {
            $response = $this->client->request(
                'POST',
                $url,
                [
                    'headers' => $headers,
                    'json' => [
                        'message' => $message,
                    ],
                    'timeout' => 5,
                ]
            );

            return $response->toArray();
        } catch (TransportExceptionInterface $e) {
            throw new InternalApiCallFailedException(
                'Internal API call to ' . $url . ' failed. Connection error: ' . $e->getMessage(),
            );
        } catch (DecodingExceptionInterface $e) {
            throw new InternalApiCallFailedException(
                'Internal API call to ' . $url . ' failed. Decoding error: ' . $e->getMessage(),
            );
        } catch (HttpExceptionInterface $e) {
            throw new InternalApiCallFailedException(
                'Internal API call to ' . $url . ' failed. Status code: ' . $response->getStatusCode() .
                ' - ' . substr($response->getContent(false), 0, 250)
            );
        }
    }

    /**
     * @param array<mixed> $data
     * @throws \Exception
     */
    public function messageFromData(array $data): string
    {
        $json = json_encode([
            'data' => $data,
            'timestamp' => time(),
        ]);
        assert(is_string($json));

        return $this->encryption->encryptString($json);
    }

    /**
     * @return array<string, mixed>
     * @throws InvalidMessageException
     */
    public function dataFromMessage(
        string $message,
        bool $validateTimestamp = true
    ): array {
        try {
            $decodedMessage = $this->encryption->decryptString($message);
        } catch (DecryptException) {
            throw new InvalidMessageException('Failed to decrypt message');
        }

        $decodedMessage = json_decode($decodedMessage, true);

        if (!is_array($decodedMessage)) {
            throw new InvalidMessageException('Invalid data');
        }

        $timestamp = $decodedMessage['timestamp'] ?? null;

        if (!is_int($timestamp)) {
            throw new InvalidMessageException('Invalid timestamp');
        }

        if ($validateTimestamp) {
            $diff = time() - $timestamp;
            if ($diff > 60) {
                throw new InvalidMessageException('Expired message');
            }
        }

        $requestData = $decodedMessage['data'] ?? [];

        if (!is_array($requestData)) {
            throw new InvalidMessageException('Data is not an array');
        }

        return $requestData;
    }

    /**
     * @return array<string, mixed>
     */
    public function dataFromMockResponse(MockResponse $mockResponse): array
    {
        $body = $mockResponse->getRequestOptions()['body'];
        return $this->dataFromMessage(json_decode($body, true, flags: JSON_THROW_ON_ERROR)['message']);
    }

    /**
     * Helper to get the requesting component from a request
     */
    public static function getRequestingComponent(Request $request): Component
    {
        $from = $request->headers->get('X-Internal-Api-From');
        assert(is_string($from));
        return Component::from($from);
    }

}
