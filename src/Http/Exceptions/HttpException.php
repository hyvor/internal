<?php

namespace Hyvor\Internal\Http\Exceptions;

use Exception;

/**
 * This exception should be thrown in case of an error in an HTTP request.
 * In most cases, this will then be converted to JSON and sent to the client.
 * @deprecated use Symfony exceptions instead.
 */
class HttpException extends Exception
{

    /**
     * @var array<string, mixed>
     */
    public $data = [];

    /**
     * @param string $message
     * @param int $code
     * @param array<string, mixed> $data
     */
    public function __construct(
        string $message,
        int $code = 0,
        array $data = []
    ) {
        $this->data = $data;
        parent::__construct($message, $code);
    }

}