<?php

namespace Hyvor\Internal\Bundle\Comms;

use Hyvor\Internal\Bundle\Comms\Event\AbstractEvent;
use Hyvor\Internal\Bundle\Comms\Exception\CommsApiFailedException;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\Component\InstanceUrlResolver;
use Hyvor\Internal\InternalConfig;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Comms
{

    use ClockAwareTrait;

    public function __construct(
        private HttpClientInterface $httpClient,
        private InternalConfig $internalConfig,
        private InstanceUrlResolver $instanceUrlResolver,
    ) {
    }

    public function signature(string $content): string
    {
        return hash_hmac('sha256', $content, $this->internalConfig->getCommsKey());
    }

    /**
     * @template TResponse of object|null
     * @template T of AbstractEvent<TResponse>
     * @param T $event
     * @param Component|null $to if null, the message's to() method MUST return exactly one component, which will be used
     * @return TResponse
     * @throws CommsApiFailedException
     */
    public function send(
        AbstractEvent $event,
        ?Component $to = null,
    ): object|null {
        $allowedFrom = $event->from();
        $allowedTo = $event->to();

        if ($to === null) {
            if (count($allowedTo) !== 1) {
                throw new \InvalidArgumentException('event to() must return exactly one component when $to is null');
            }
            $to = $allowedTo[0];
        }

        $from = $this->internalConfig->getComponent();

        if (!empty($allowedFrom) && !in_array($from, $allowedFrom, true)) {
            throw new \InvalidArgumentException("event is not allowed to be sent from component {$from->value}");
        }

        if (!empty($allowedTo) && !in_array($to, $allowedTo, true)) {
            throw new \InvalidArgumentException("event is not allowed to be sent to component {$to->value}");
        }

        $componentUrl = $this->instanceUrlResolver->privateUrlOf($to);
        $url = $componentUrl . '/api/comms/event';

        $data = [
            'at' => $this->now()->getTimestamp(),
            'event' => serialize($event),
        ];

        $jsonPayload = json_encode($data, JSON_THROW_ON_ERROR);

        $headers = [
            'Content-Type' => 'application/json',
            'X-Signature' => $this->signature($jsonPayload),
        ];

        try {
            $response = $this->httpClient->request(
                'POST',
                $url,
                [
                    'headers' => $headers,
                    'json' => $data,
                ]
            );

            $output = $response->toArray();

            /** @var TResponse $response */
            $response = unserialize($output['response']);

            return $response;
        } catch (TransportExceptionInterface $e) { // @codeCoverageIgnoreStart
            throw new CommsApiFailedException(
                'comms event to ' . $url . ' failed. Connection error: ' . $e->getMessage(),
            );
        } catch (DecodingExceptionInterface $e) {
            throw new CommsApiFailedException(
                'comms event to ' . $url . ' failed. Decoding error: ' . $e->getMessage(),
            );
        } catch (HttpExceptionInterface $e) {
            throw new CommsApiFailedException(
                'comms event to ' . $url . ' failed. Status code: ' . $response->getStatusCode() .
                ' - ' . substr($response->getContent(false), 0, 250)
            );
        }
        // @codeCoverageIgnoreEnd
    }

}