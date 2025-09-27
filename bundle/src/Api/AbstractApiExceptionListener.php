<?php

namespace Hyvor\Internal\Bundle\Api;

use Psr\Log\LoggerInterface;
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
        private LoggerInterface $logger,
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

                $data['message'] = $violations[0]['message'] ?? 'Validation Failed';
                $data['status'] = Response::HTTP_UNPROCESSABLE_ENTITY;
                $data['violations'] = $violations;
            }

            if ($exception instanceof DataCarryingHttpException) {
                $data['data'] = $exception->getData();
            }
        } else {
            // this is an unhandled exception
            if ($shouldThrowOnInternalError) {
                // let symfony handle the exception
                return;
            } else {
                // log the exception
                $this->logger->critical(
                    'Unhandled exception in API',
                    [
                        'exception' => $exception,
                        'request' => [
                            'method' => $event->getRequest()->getMethod(),
                            'path' => $event->getRequest()->getPathInfo(),
                            'query' => $event->getRequest()->query->all(),
                            'body' => $event->getRequest()->request->all(),
                            'headers' => $this->stripSensitiveHeaders($event->getRequest()->headers->all()),
                        ],
                    ]
                );
            }
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

    /**
     * @param array<string, list<string|null>> $headers
     * @return array<string, list<string|null>>
     */
    private function stripSensitiveHeaders(array $headers): array
    {
        $sensitiveHeaders = ['authorization', 'cookie', 'set-cookie', 'x-api-key'];
        foreach ($sensitiveHeaders as $header) {
            unset($headers[$header]);
        }
        return $headers;
    }

}
