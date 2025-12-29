<?php

namespace Hyvor\Internal\Bundle\Comms\Message;

use Hyvor\Internal\Component\Component;

interface MessageInterface
{

    /**
     * [Component::TALK, Component::Blogs] = allow from both Talk and Blogs
     * [] = allows from any component
     * @return Component[]
     */
    public function from(): array;

    /**
     * @return Component[]
     */
    public function to(): array;

}