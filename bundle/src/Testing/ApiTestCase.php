<?php

namespace Hyvor\Internal\Bundle\Testing;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ApiTestCase extends WebTestCase
{

    use ApiTestingTrait;

    protected KernelBrowser $client;
    protected ContainerInterface $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->container = static::getContainer();
    }

}