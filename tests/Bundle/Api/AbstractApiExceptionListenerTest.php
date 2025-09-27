<?php

namespace Hyvor\Internal\Tests\Bundle\Api;

use Hyvor\Internal\Bundle\Api\AbstractApiExceptionListener;
use Hyvor\Internal\Bundle\Api\DataCarryingHttpException;
use Hyvor\Internal\Tests\SymfonyTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[CoversClass(AbstractApiExceptionListener::class)]
#[CoversClass(DataCarryingHttpException::class)]
class AbstractApiExceptionListenerTest extends SymfonyTestCase
{

    private function getLogger(): LoggerInterface&MockObject
    {
        return $this->createMock(LoggerInterface::class);
    }

    public function test_matches_prefix(): void
    {
        $listener = new ApiExceptionListener('dev', $this->getLogger());

        $exceptionEvent = new ExceptionEvent(
            $this->kernel,
            new Request(
                server: [
                    'REQUEST_METHOD' => 'GET',
                    'REQUEST_URI' => '/api/public/some/endpoint',
                ]
            ),
            0,
            new \Exception('Test exception')
        );

        $listener($exceptionEvent);
        $this->assertNull($exceptionEvent->getResponse());
    }

    public function test_validation_exception(): void
    {
        $listener = new ApiExceptionListener('dev', $this->getLogger());

        $exceptionEvent = new ExceptionEvent(
            $this->kernel,
            new Request(
                server: [
                    'REQUEST_METHOD' => 'GET',
                    'REQUEST_URI' => '/api/console/some/endpoint',
                ]
            ),
            0,
            new UnprocessableEntityHttpException(
                'invalid email', previous: new ValidationFailedException(
                'email validation failed',
                violations: new ConstraintViolationList([
                    new ConstraintViolation(
                        'email should contain @',
                        null,
                        [],
                        null,
                        'subscriber.email',
                        'invalidemail'
                    ),
                    new ConstraintViolation(
                        'type should by of type App\Enum\TestEnum',
                        null,
                        [],
                        null,
                        'type',
                        'sometype'
                    ),
                ])
            )
            )
        );

        $listener($exceptionEvent);

        $response = $exceptionEvent->getResponse();
        $this->assertNotNull($response);
        $data = json_decode((string)$response->getContent(), true, JSON_THROW_ON_ERROR);
        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('email should contain @', $data['message']);
        $this->assertSame('subscriber.email', $data['violations'][0]['property']);
        $this->assertSame('email should contain @', $data['violations'][0]['message']);

        $this->assertSame('type', $data['violations'][1]['property']);
        $this->assertSame('type should by of type test|test2|test3', $data['violations'][1]['message']);
    }

    public function test_does_not_set_response_for_unknown_errors_on_dev(): void
    {
        $listener = new ApiExceptionListener('dev', $this->getLogger());

        $exceptionEvent = new ExceptionEvent(
            $this->kernel,
            new Request(
                server: [
                    'REQUEST_METHOD' => 'GET',
                    'REQUEST_URI' => '/api/console/some/endpoint',
                ]
            ),
            0,
            new \Exception('Very bad thing')
        );

        $listener($exceptionEvent);
        $this->assertNull($exceptionEvent->getResponse());
    }

    public function test_logs_on_500_error_on_prod(): void
    {
        $logger = $this->getLogger();
        $message = '';
        $context = [];
        $logger->method('critical')
            ->willReturnCallback(function ($m, $c) use (&$message, &$context) {
                $message = $m;
                $context = $c;
            });

        $listener = new ApiExceptionListener('prod', $logger);

        $exceptionEvent = new ExceptionEvent(
            $this->kernel,
            new Request(
                server: [
                    'REQUEST_METHOD' => 'GET',
                    'REQUEST_URI' => '/api/console/some/endpoint',
                    'HTTP_CONTENT_TYPE' => 'application/json',
                    'HTTP_AUTHORIZATION' => 'Bearer some-token',
                ]
            ),
            0,
            new \Exception('Test exception')
        );

        $listener($exceptionEvent);

        $response = $exceptionEvent->getResponse();
        $this->assertNotNull($response);
        $data = json_decode((string)$response->getContent(), true, JSON_THROW_ON_ERROR);
        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame('Internal Server Error. Our team has been notified.', $data['message']);

        $this->assertSame('Unhandled exception in API', $message);
        $this->assertInstanceOf(\Exception::class, $context['exception']);
        $request = $context['request'];
        $this->assertIsArray($request);
        $this->assertSame('GET', $request['method']);
        $this->assertSame('/api/console/some/endpoint', $request['path']);

        $headers = $request['headers'];
        $this->assertArrayHasKey('content-type', $headers);
        $this->assertSame(['application/json'], $headers['content-type']);
        $this->assertArrayNotHasKey('authorization', $headers);
    }

    public function test_data_carrying_http_exception(): void
    {
        $listener = new ApiExceptionListener('dev', $this->getLogger());

        $exceptionEvent = new ExceptionEvent(
            $this->kernel,
            new Request(
                server: [
                    'REQUEST_METHOD' => 'GET',
                    'REQUEST_URI' => '/api/console/some/endpoint',
                ]
            ),
            0,
            new DataCarryingHttpException(
                400,
                ['foo' => 'bar'],
                'Custom error message'
            )
        );

        $listener($exceptionEvent);

        $response = $exceptionEvent->getResponse();
        $this->assertNotNull($response);
        $data = json_decode((string)$response->getContent(), true, JSON_THROW_ON_ERROR);
        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Custom error message', $data['message']);
        $this->assertSame(['foo' => 'bar'], $data['data']);
    }

}


class ApiExceptionListener extends AbstractApiExceptionListener
{
    protected function prefix(): string
    {
        return '/api/console';
    }
}


// Helpers

namespace App\Enum;

enum TestEnum: string
{
    case TEST = 'test';
    case TEST2 = 'test2';
    case TEST3 = 'test3';
}
