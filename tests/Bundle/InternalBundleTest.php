<?php

namespace Hyvor\Internal\Tests\Bundle;

use Hyvor\Internal\Auth\Auth;
use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Billing\Billing;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\BillingInterface;
use Hyvor\Internal\Bundle\InternalBundle;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\InternalConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\Definition\Loader\DefinitionFileLoader;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

#[CoversClass(InternalBundle::class)]
class InternalBundleTest extends TestCase
{

    public function test_configure(): void
    {
        $treeBuilder = new TreeBuilder('tree');
        $definitionFileLoader = new DefinitionFileLoader($treeBuilder, $this->createMock(FileLocatorInterface::class));
        $definitionConfigurator = new DefinitionConfigurator($treeBuilder, $definitionFileLoader, 'path', 'file');

        $bundle = new InternalBundle();
        $bundle->configure($definitionConfigurator);

        $rootNode = $definitionConfigurator->rootNode();
        $this->assertInstanceOf(ArrayNodeDefinition::class, $rootNode);
        $childNodes = $rootNode->getChildNodeDefinitions();
        $this->assertArrayHasKey('component', $childNodes);
        $this->assertArrayHasKey('i18n', $childNodes);
    }

    public function test_extension(): void
    {
        $instanceof = [];
        $containerBuilder = new ContainerBuilder();
        $containerConfigurator = new ContainerConfigurator(
            $containerBuilder,
            $this->createMock(PhpFileLoader::class),
            $instanceof,
            'path',
            'file',
            null
        );

        $containerConfigurator->services()->set(InternalConfig::class, InternalConfig::class);
        $containerConfigurator->services()->set(Auth::class, Auth::class);
        $containerConfigurator->services()->set(AuthFake::class, AuthFake::class);
        $containerConfigurator->services()->set(Billing::class, Billing::class);
        $containerConfigurator->services()->set(BillingFake::class, BillingFake::class);

        $containerConfigurator->parameters()->set('kernel.project_dir', '/path/to/project');

        $bundle = new InternalBundle();
        $config = [
            'component' => 'core',
            'instance' => 'https://hyvor.com',
            'auth_method' => 'hyvor',
            'private_instance' => null,
            'fake' => false,
            'i18n' => [
                'folder' => '%kernel.project_dir%/../shared/locale',
                'default' => 'en-US',
            ],
        ];
        $bundle->loadExtension($config, $containerConfigurator, $containerBuilder);

        $internalConfig = $containerBuilder->get(InternalConfig::class);
        $this->assertInstanceOf(InternalConfig::class, $internalConfig);
    }
}
