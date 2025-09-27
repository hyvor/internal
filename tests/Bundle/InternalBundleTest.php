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
        $this->assertArrayHasKey('instance', $childNodes);
        $this->assertArrayHasKey('private_instance', $childNodes);
        $this->assertArrayHasKey('fake', $childNodes);
        $this->assertArrayHasKey('i18n', $childNodes);
    }

    private function getContainerBuilder(bool $dev = false): ContainerBuilder
    {
        $instanceof = [];
        $containerBuilder = new ContainerBuilder();
        $containerConfigurator = new ContainerConfigurator(
            $containerBuilder,
            $this->createMock(PhpFileLoader::class),
            $instanceof,
            'path',
            'file',
            $dev ? 'dev' : null
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
            'fake' => $dev,
            'i18n' => [
                'folder' => '%kernel.project_dir%/../shared/locale',
                'default' => 'en-US',
            ],
        ];
        $bundle->loadExtension($config, $containerConfigurator, $containerBuilder);

        return $containerBuilder;
    }

    public function test_load_extension(): void
    {
        $containerBuilder = $this->getContainerBuilder();

        // internal config
        $internalConfig = $containerBuilder->get(InternalConfig::class);
        $this->assertInstanceOf(InternalConfig::class, $internalConfig);
        $this->assertSame(Component::CORE, $internalConfig->getComponent());
        $this->assertSame('https://hyvor.com', $internalConfig->getInstance());
        $this->assertSame(null, $internalConfig->getPrivateInstance());
        $this->assertFalse($internalConfig->isFake());
        $this->assertSame('/path/to/project/../shared/locale', $internalConfig->getI18nFolder());
        $this->assertSame('en-US', $internalConfig->getI18nDefaultLocale());

        // bindings
        $this->assertSame(Auth::class, (string)$containerBuilder->getAlias(AuthInterface::class));
        $this->assertSame(Billing::class, (string)$containerBuilder->getAlias(BillingInterface::class));
    }

    public function test_load_extension_on_dev(): void
    {
        $_ENV['HYVOR_FAKE'] = '1';
        $containerBuilder = $this->getContainerBuilder(true);
        $this->assertSame(AuthFake::class, (string)$containerBuilder->getAlias(AuthInterface::class));
    }

}
