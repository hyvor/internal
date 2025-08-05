<?php

namespace Hyvor\Internal\Tests\Bundle\Api;

use Hyvor\Internal\Bundle\Api\AbstractApiExceptionListener;
use Hyvor\Internal\Bundle\Api\DataCarryingHttpException;
use Hyvor\Internal\Tests\SymfonyTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[CoversClass(AbstractApiExceptionListener::class)]
#[CoversClass(DataCarryingHttpException::class)]
class AbstractApiExceptionListenerTest extends SymfonyTestCase
{

    public function test_matches_prefix(): void
    {
        $listener = new ApiExceptionListener('dev');

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
        $listener = new ApiExceptionListener('dev');

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
        $this->assertSame('Validation failed with 2 violations(s)', $data['message']);
        $this->assertSame('subscriber.email', $data['violations'][0]['property']);
        $this->assertSame('email should contain @', $data['violations'][0]['message']);

        $this->assertSame('type', $data['violations'][1]['property']);
        $this->assertSame('type should by of type test|test2|test3', $data['violations'][1]['message']);
    }

    public function test_does_not_set_response_for_internal_server_errors_on_dev(): void
    {
        $listener = new ApiExceptionListener('dev');

        $exceptionEvent = new ExceptionEvent(
            $this->kernel,
            new Request(
                server: [
                    'REQUEST_METHOD' => 'GET',
                    'REQUEST_URI' => '/api/console/some/endpoint',
                ]
            ),
            0,
            new HttpException(500)
        );

        $listener($exceptionEvent);
        $this->assertNull($exceptionEvent->getResponse());
    }

    public function test_data_carrying_http_exception(): void
    {
        $listener = new ApiExceptionListener('dev');

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