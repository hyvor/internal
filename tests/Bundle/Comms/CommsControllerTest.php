<?php

namespace Hyvor\Internal\Tests\Bundle\Comms;

use Hyvor\Internal\Bundle\Comms\Comms;
use Hyvor\Internal\Bundle\Comms\Controller\CommsController;
use Hyvor\Internal\Tests\Bundle\Comms\TestEvent\TestEventToCore;
use Hyvor\Internal\Tests\SymfonyTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[CoversClass(CommsController::class)]
class CommsControllerTest extends SymfonyTestCase
{

    private function comms(): Comms
    {
        /** @var Comms $comms */
        $comms = $this->container->get(Comms::class);
        return $comms;
    }

    public function test_fails_on_invalid_signature(): void
    {
        $request = Request::create('/api/comms/event', 'POST', [
            'event' => 'serialized',
            'at' => time()
        ]);
        $request->headers->set('X-Signature', 'wrong');

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('invalid signature');

        $this->kernel->handle($request, catch: false);
    }

    #[TestWith([350])]
    #[TestWith([-350])]
    public function test_fails_when_timestamp_too_long(int $change): void
    {
        $payload = json_encode([
            'event' => 'serialized',
            'at' => time() + $change
        ], JSON_THROW_ON_ERROR);
        $request = Request::create('/api/comms/event', 'POST', content: $payload);
        $request->headers->set('Content-Type', 'application/json');
        $request->headers->set('X-Signature', $this->comms()->signature($payload));

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('invalid payload: timestamp out of range');

        $this->kernel->handle($request, catch: false);
    }

    public function test_fails_on_invalid_event(): void
    {
        $payload = json_encode([
            'event' => 'serialized',
            'at' => time()
        ], JSON_THROW_ON_ERROR);

        $request = Request::create('/api/comms/event', 'POST', content: $payload);
        $request->headers->set('Content-Type', 'application/json');
        $request->headers->set('X-Signature', $this->comms()->signature($payload));

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('invalid event received: unable to unserialize');

        $this->kernel->handle($request, catch: false);
    }

    public function test_dispatches_event_and_sends_response(): void
    {
        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->container->get('event_dispatcher');

        $userId = null;

        $dispatcher->addListener(TestEventToCore::class, function (TestEventToCore $event) use (&$userId) {
            $userId = $event->id;

            $event->setResponse(
                (object)[
                    'big' => 'bang'
                ]
            );
        });

        $event = new TestEventToCore(1);
        $payload = json_encode([
            'event' => serialize($event),
            'at' => time()
        ], JSON_THROW_ON_ERROR);

        $request = Request::create('/api/comms/event', 'POST', content: $payload);
        $request->headers->set('Content-Type', 'application/json');
        $request->headers->set('X-Signature', $this->comms()->signature($payload));

        $response = $this->kernel->handle($request, catch: false);

        $this->assertSame(200, $response->getStatusCode());
        $responseObj = json_decode((string)$response->getContent(), true)['response'];
        $responseDeserialized = unserialize($responseObj);

        $this->assertSame('bang', $responseDeserialized->big);
        $this->assertSame(1, $userId);
    }

    public function test_dispatches_event_and_sends_error(): void
    {
        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->container->get('event_dispatcher');

        $dispatcher->addListener(TestEventToCore::class, function (TestEventToCore $event) {
            $event->setError(
                'big bang did not happen',
                422
            );
        });

        $event = new TestEventToCore(1);
        $payload = json_encode([
            'event' => serialize($event),
            'at' => time()
        ], JSON_THROW_ON_ERROR);

        $request = Request::create('/api/comms/event', 'POST', content: $payload);
        $request->headers->set('Content-Type', 'application/json');
        $request->headers->set('X-Signature', $this->comms()->signature($payload));

        $response = $this->kernel->handle($request);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertStringContainsString(
            '{"message":"big bang did not happen","status":422}',
            (string)$response->getContent()
        );
    }

}