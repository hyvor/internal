<?php

namespace Hyvor\Internal\Bundle\Comms\Controller;

use Hyvor\Internal\Bundle\Comms\CommsInterface;
use Hyvor\Internal\Bundle\Comms\Event\AbstractEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;

class CommsController extends AbstractController
{

    public function __construct(
        private EventDispatcherInterface $ed,
        private CommsInterface $comms
    ) {
    }

    #[Route('/api/comms/event', methods: 'POST')]
    public function event(
        #[MapRequestPayload] CommsEventInput $input,
        Request $request
    ): JsonResponse {
        $jsonPayload = $request->getContent();
        $signature = $request->headers->get('x-signature');
        $expectedSignature = $this->comms->signature($jsonPayload);

        if ($signature !== $expectedSignature) {
            throw new BadRequestHttpException('invalid signature');
        }

        if ($input->at < time() - 300 || $input->at > time() + 300) {
            throw new BadRequestHttpException('invalid payload: timestamp out of range');
        }

        $eventSerialized = $input->event;
        $event = unserialize($eventSerialized);

        if (!$event instanceof AbstractEvent) {
            throw new BadRequestHttpException('invalid event received: unable to unserialize');
        }

        $this->ed->dispatch($event);

        $error = $event->getError();
        if ($error !== null) {
            throw new HttpException($error['code'], $error['message']);
        }

        $response = $event->getResponse();

        return new JsonResponse([
            'response' => serialize($response)
        ]);
    }

}
