<?php

namespace Hyvor\Internal\Bundle\Comms\Event;

use Hyvor\Internal\Component\Component;

/**
 * @template TResponse of object|null = null
 */
abstract class AbstractEvent
{

    /**
     * @var TResponse
     */
    private ?object $response = null;

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