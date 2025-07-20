<?php

namespace Hyvor\Internal\Auth\Oidc\Exception;

class UnableToFetchWellKnownException extends \Exception
{

    public function __construct(public string $discoveryUrl, string $message = "", ?\Throwable $previous = null)
    {
        parent::__construct($message, previous: $previous);
    }

}