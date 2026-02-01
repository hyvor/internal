<?php

namespace Hyvor\Internal\Bundle\Comms\Event;

use Hyvor\Internal\Component\Component;

/**
 * @template TResponse of object|null = null
 * @phpstan-type ErrorType array{message:string,code:int}
 */
abstract class AbstractEvent
{

    /**
     * @var TResponse
     */
    private ?object $response = null;

    /** @var ErrorType|null */
    private ?array $error = null;

    /**
     * @param TResponse $response
     */
    public function setResponse(?object $response): void
    {
        $this->response = $response;
    }

    /**
     * @return TResponse
     */
    public function getResponse(): ?object
    {
        return $this->response;
    }

    public function setError(string $message, int $code = 400): void
    {
        $this->error = [
            'message' => $message,
            'code' => $code
        ];
    }

    /**
     * @return ErrorType|null
     */
    public function getError(): ?array
    {
        return $this->error;
    }

    /**
     * [Component::TALK, Component::Blogs] = allow from both Talk and Blogs
     * [] = allows from any component
     * @return Component[]
     */
    abstract public function from(): array;

    /**
     * @return Component[]
     */
    abstract public function to(): array;

}