<?php

namespace Hyvor\Internal\Bundle\Testing;

use Symfony\Component\DependencyInjection\ContainerInterface;

class KernelTestCase extends \Symfony\Bundle\FrameworkBundle\Test\KernelTestCase
{

    use BaseTestingTrait;

    protected ContainerInterface $container;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->container = static::getContainer();
    }

}