<?php

namespace Hyvor\Internal\Tests;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SymfonyTestBundle extends AbstractBundle
{

    /**
     * @param array<mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container
            ->services()
            ->set('kernel', SymfonyKernel::class)
            ->set(HttpClientInterface::class, MockHttpClient::class)
            ->set(CacheInterface::class, ArrayAdapter::class)
            ->set(EntityManagerInterface::class, EntityManager::class)
            ->set(ManagerRegistry::class, Registry::class)
            ->public();
    }

}