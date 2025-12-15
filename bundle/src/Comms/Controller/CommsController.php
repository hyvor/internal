<?php

namespace Hyvor\Internal\Bundle\Comms\Controller;

use Hyvor\Internal\Bundle\Comms\Event\AbstractEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;

class CommsController extends AbstractController
{

    public function __construct(
        private EventDispatcherInterface $ed,
    ) {
    }

    #[Route('/api/comms/event', methods: 'POST')]
    public function event(
        #[MapRequestPayload] CommsEventInput $input
    ): JsonResponse {
        $eventSerialized = $input->event;

        $event = false;
        try {
            $event = unserialize($eventSerialized);
        } catch (\Exception) {
        }

        if (!$event instanceof AbstractEvent) {
            throw new BadRequestHttpException('Invalid event received');
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