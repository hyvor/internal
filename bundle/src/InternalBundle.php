<?php

namespace Hyvor\Internal\Bundle;

use Hyvor\Internal\Auth\AuthFactory;
use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Billing\BillingFactory;
use Hyvor\Internal\Billing\BillingInterface;
use Hyvor\Internal\Bundle\EventDispatcher\EventDispatcherCompilerPass;
use Hyvor\Internal\InternalConfig;
use Hyvor\Internal\SelfHosted\SelfHostedTelemetry;
use Hyvor\Internal\SelfHosted\SelfHostedTelemetryInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class InternalBundle extends AbstractBundle
{

    protected string $extensionAlias = 'internal';

    public function configure(DefinitionConfigurator $definition): void
    {
        //@formatter:off
        /**
         * component: string
         * instance: string
         * private_instance: string
         * fake: bool
         */
        $definition->rootNode()
            ->children()
                ->scalarNode('component')->defaultValue('core')->end()
                ->arrayNode('i18n')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('folder')->defaultValue('%kernel.project_dir%/../shared/locale')->end()
                        ->scalarNode('default')->defaultValue('en-US')->end()
                    ->end()
                ->end()
            ->end();
        // @formatter:on
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        if ($container->getParameter('kernel.environment') === 'test') {
            $container->addCompilerPass(new EventDispatcherCompilerPass());
        }
    }

    /**
     * @param array<mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // SERVICES
        $container->import('../config/services.php');

        $container->parameters()
            ->set('internal.default_auth_method', 'oidc')
            ->set('internal.default_instance', 'https://hyvor.com')
            ->set('internal.default_private_instance', null)
            ->set('internal.default_fake', false);

        // InternalConfig class
        $container->services()
            ->get(InternalConfig::class)
            ->args([
                '%env(APP_SECRET)%',
                '%env(string:default::COMMS_KEY)%',
                $config['component'],
                '%env(default:internal.default_auth_method:AUTH_METHOD)%',
                '%env(default:internal.default_instance:HYVOR_INSTANCE)%',
                '%env(default:internal.default_private_instance:HYVOR_PRIVATE_INSTANCE)%',
                '%env(bool:default:internal.default_fake:HYVOR_FAKE)%',
                $config['i18n']['folder'],
                $config['i18n']['default'],
            ]);

        $container
            ->services()
            ->set(AuthInterface::class)
            ->factory([service(AuthFactory::class), 'create']);

        $container
            ->services()
            ->set(BillingInterface::class)
            ->public() // because this is not used from outside, so tests fail (inlined)
            ->factory([service(BillingFactory::class), 'create']);

        // other services
        $container->services()->alias(SelfHostedTelemetryInterface::class, SelfHostedTelemetry::class);
    }

}
