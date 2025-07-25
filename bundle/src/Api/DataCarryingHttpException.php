<?php

namespace Hyvor\Internal\Bundle\Api;

/**
 * This exception carries additional data along with the HTTP status code and message.
 * AbstractApiExceptionListener adds this data to the response.
 */
class DataCarryingHttpException extends \Symfony\Component\HttpKernel\Exception\HttpException
{

    /**
     * Additional data
     * @var array<string, mixed>
     */
    private array $data;

    /**
     * @param array<string, mixed> $data
     * @param array<mixed> $headers
     */
    public function __construct(
        int $status,
        array $data,
        string $message = '',
        ?\Throwable $previous = null,
        int $code = 0,
        array $headers = []
    ) {
        $this->data = $data;
        parent::__construct($status, $message, $previous, $headers, $code);
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

}