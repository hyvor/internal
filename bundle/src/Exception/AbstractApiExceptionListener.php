<?php

namespace Hyvor\Internal\Bundle\Exception;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * Make sure to add the following attribute to the class:
 *
 * use Symfony\Component\HttpKernel\KernelEvents;
 * use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
 *
 * #[AsEventListener(event: KernelEvents::EXCEPTION)]
 */
abstract class AbstractApiExceptionListener
{

    public function __construct(
        #[Autowire('%kernel.environment%')]
        private string $env,
    ) {
    }

    /**
     * API Prefix
     * ex: /api/console
     */
    abstract protected function prefix(): string;

    public function __invoke(ExceptionEvent $event): void
    {
        $shouldThrowOnInternalError = $this->env === 'test' || $this->env === 'dev';

        $path = $event->getRequest()->getPathInfo();

        if (!str_starts_with($path, $this->prefix())) {
            return;
        }

        $exception = $event->getThrowable();

        $response = new JsonResponse();

        $data = [
            'message' => 'Internal Server Error. Our team has been notified.',
            'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
        ];

        if ($exception instanceof HttpExceptionInterface) {
            $response->headers->replace($exception->getHeaders());
            $data['message'] = $exception->getMessage();
            $data['status'] = $exception->getStatusCode();

            $previous = $exception->getPrevious();
            if ($previous instanceof ValidationFailedException) {
                $violations = [];

                foreach ($previous->getViolations() as $violation) {
                    $violations[] = [
                        'property' => $violation->getPropertyPath(),
                        'message' => $this->hideEnum($violation->getMessage()),
                    ];
                }

                $data['message'] = 'Validation failed with ' . count($violations) . ' violations(s)';
                $data['status'] = Response::HTTP_UNPROCESSABLE_ENTITY;
                $data['violations'] = $violations;
            }
        }

        if ($shouldThrowOnInternalError && $data['status'] === Response::HTTP_INTERNAL_SERVER_ERROR) {
            return;
        }

        $response->setData($data);
        $response->setStatusCode($data['status']);

        $event->setResponse($response);
    }

    private function hideEnum(string $message): string
    {
        // This value should be of type App\Enum\SubscriberStatus
        // This value should be of type subscribed|unsubscribed|pending.
        $message = preg_replace_callback(
            '/App\\\\[A-Za-z0-9_\\\\]+/',
            function ($matches) {
                $class = $matches[0];
                // it should definitely be an enum
                assert(enum_exists($class));
                $values = array_column($class::cases(), 'value');
                return implode('|', $values);
            },
            $message
        );

        return (string)$message;
    }

}