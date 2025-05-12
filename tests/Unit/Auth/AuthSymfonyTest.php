<?php

namespace Hyvor\Internal\Tests\Unit\Auth;

use Hyvor\Internal\Tests\SymfonyTestCase;

class AuthSymfonyTest extends SymfonyTestCase
{
    use AuthTestTrait;

    protected function getContainer(): \Symfony\Component\DependencyInjection\Container
    {
        return $this->container;
    }
}