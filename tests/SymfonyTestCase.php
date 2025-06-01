<?php

namespace Hyvor\Internal\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

// https://symfonycasts.com/screencast/symfony-bundle/integration-test
class SymfonyTestCase extends TestCase
{

    public SymfonyKernel $kernel;
    public Container $container;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = new SymfonyKernel('test', true);
        $kernel->boot();
        $container = $kernel->getContainer();
        assert($container instanceof Container);

        $this->kernel = $kernel;
        $this->container = $container;
        $this->container->set(HttpClientInterface::class, new MockHttpClient());
    }

    protected function getContainer(): Container
    {
        return $this->container;
    }

}