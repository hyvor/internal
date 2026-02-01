<?php

namespace Hyvor\Internal\Tests\Bundle\Comms;

use Hyvor\Internal\Bundle\Comms\Comms;
use Hyvor\Internal\Bundle\Comms\Event\AbstractEvent;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\Tests\Helper\UpdatesInternalConfig;
use Hyvor\Internal\Tests\SymfonyTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(Comms::class)]
class CommsTest extends SymfonyTestCase
{

    use UpdatesInternalConfig;
    use ClockSensitiveTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->updateInternalConfig('commsKey', 'xLsMjPgwsxg2VMt9XtIF+spm2fDDJ3c1/BhrfMaFgtU=');
    }

    private function comms(): Comms
    {
        /** @var Comms $comms */
        $comms = $this->container->get(Comms::class);
        return $comms;
    }

    /**
     * @param Component[] $to
     * @param Component[] $from
     * @return AbstractEvent
     */
    private function event(
        array $to = [],
        array $from = [],
    ): AbstractEvent {
        return new class($to, $from) extends AbstractEvent {
            public function __construct(
                /**
                 * @var Component[]
                 */
                private array $to,
                /**
                 * @var Component[]
                 */
                private array $from,
            ) {
            }

            public function from(): array
            {
                return $this->from;
            }

            public function to(): array
            {
                return $this->to;
            }
        };
    }

    public function test_gets_signature(): void
    {
        $signature = $this->comms()->signature('hello');
        $this->assertSame('da59d514b3f136c06912e83f9792e385023f12edbe21f163a876fe193f5492fc', $signature);
    }

    public function test_send_validates_to_has_one_when_to_is_not_set(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('event to() must return exactly one component when $to is null');

        $this->comms()->send($this->event());
    }

    public function test_validates_from(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('event is not allowed to be sent from component blogs');

        $this->updateInternalConfig('component', 'blogs');
        $this->comms()->send($this->event(from: [Component::TALK]), Component::CORE);
    }

    public function test_validates_to(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('event is not allowed to be sent to component core');
        $this->comms()->send($this->event(to: [Component::TALK]), Component::CORE);
    }

    public function test_sends(): void
    {
        $clock = static::mockTime('2025-06-01');

        $response = new JsonMockResponse([
            'response' => serialize((object)['hello' => 'world'])
        ]);
        $httpClient = new MockHttpClient($response);
        $this->container->set(HttpClientInterface::class, $httpClient);

        $event = new TestEvent\TestEventToCore(1);

        // automatically sent to CORE
        $eventResponse = $this->comms()->send($event);

        // @phpstan-ignore-next-line
        $this->assertSame('world', $eventResponse->hello);

        $this->assertSame(
            'https://hyvor.internal/api/comms/event',
            $response->getRequestUrl()
        );
        $this->assertSame('POST', $response->getRequestMethod());

        $headers = $response->getRequestOptions()['headers'];
        $this->assertContains(
            'Content-Type: application/json',
            $headers
        );
        $this->assertContains(
            'X-Signature: ab9299edb9e23a9a89158e734b57cf72e7ef23756ff0fbde146a8f31731d6d48',
            $headers
        );

        $body = $response->getRequestOptions()['body'];
        $json = json_decode($body, true);

        $this->assertSame(1748736000, $json['at']);

        $eventSerialized = $json['event'];
        $event = unserialize($eventSerialized);

        $this->assertInstanceOf(TestEvent\TestEventToCore::class, $event);
        $this->assertSame(1, $event->id);
    }
}
