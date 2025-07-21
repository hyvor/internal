<?php

namespace Hyvor\Internal\Tests;

use Hyvor\Internal\Bundle\InternalBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class SymfonyKernel extends Kernel
{

    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        return [
            new SymfonyTestBundle(),
            new InternalBundle(),
            new FrameworkBundle(), // needed for HttpKernel
        ];
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('../bundle/src/Controller/*.php', 'attribute');
    }
}