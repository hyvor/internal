<?php

namespace Hyvor\Internal\Tests;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Hyvor\Internal\Bundle\InternalBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Zenstruck\Foundry\ZenstruckFoundryBundle;

class SymfonyKernel extends Kernel
{

    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        return [
            new SymfonyTestBundle(),
            new InternalBundle(),
            new FrameworkBundle(), // needed for HttpKernel
            new DoctrineBundle(),
            new ZenstruckFoundryBundle(),
        ];
    }

    protected function configureContainer(
        ContainerConfigurator $container,
        LoaderInterface $loader,
        ContainerBuilder $builder
    ): void {
        $container->extension('framework', [
            'test' => true,
            'cache' => [
                'app' => 'cache.adapter.array',
            ]
        ]);

        // set up doctrine with SQLite in memory
        $container->extension('doctrine', [
            'dbal' => [
                'driver' => 'pdo_sqlite',
                'memory' => true,
            ],
            'orm' => [
                'auto_generate_proxy_classes' => true,
                'entity_managers' => [
                    'default' => [
                        'mappings' => [
                            'InternalBundle' => [
                                'type' => 'attribute',
                                'dir' => '%kernel.project_dir%/bundle/src/Entity',
                                'prefix' => 'Hyvor\\Internal\\Bundle\\Entity',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        // foundry configuration
        $container->extension('zenstruck_foundry', [
            'persistence' => [
                'flush_once' => true,
            ],
        ]);
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('../bundle/src/Controller/*.php', 'attribute')
            ->prefix('/api/oidc');
    }
}