<?php

namespace Hyvor\Internal\InternalApi;

use Hyvor\Internal\Bundle\InternalConfig;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\Component\ComponentUrlResolver;
use Hyvor\Internal\InternalApi\Exceptions\InternalApiCallFailedException;
use Hyvor\Internal\InternalApi\Exceptions\InvalidMessageException;
use Hyvor\Internal\Util\Crypt\Encryption;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
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
    ) {
    }

    /**
     * @param array<mixed> $data
     * @param InternalApiMethod|'GET'|'POST' $method
     * @return array<mixed>
     * @throws InternalApiCallFailedException
     */
    public function call(
        Component $to,
        InternalApiMethod|string $method,
        /**
         * This is the part after the `/api/internal/` in the URL
         * ex: set `/delete-user` to call `/api/internal/delete-user`
         */
        string $endpoint,
        array $data = [],
        ?Component $from = null
    ): array {
        if (is_string($method)) {
            $method = InternalApiMethod::from($method);
        }

        $endpoint = ltrim($endpoint, '/');
        $componentUrl = new ComponentUrlResolver($this->config->getPrivateInstanceWithFallback())->of($to);

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
                $method->value,
                $url,
                [
                    'headers' => $headers,
                    'body' => [
                        'message' => $message,
                    ],
                ]
            );

            $status = $response->getStatusCode();

            if ($status !== 200) {
                throw new InternalApiCallFailedException(
                    'Internal API call to ' . $url . ' failed. Status code: ' . $status .
                    ' - ' . substr($response->getContent(), 0, 250)
                );
            }

            return $response->toArray();
        } catch (TransportExceptionInterface $e) {
            throw new InternalApiCallFailedException(
                'Internal API call to ' . $url . ' failed. Connection error: ' . $e->getMessage(),
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
     */
    public static function dataFromMessage(
        string $message,
        bool $validateTimestamp = true
    ): array {
        try {
            $decodedMessage = Crypt::decryptString($message);
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
     * Helper to get the requesting component from a request
     */
    public static function getRequestingComponent(Request $request): Component
    {
        $from = $request->header('X-Internal-Api-From');
        assert(is_string($from));
        return Component::from($from);
    }

}
