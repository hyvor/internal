<?php

namespace Hyvor\Internal\Bundle;

use Hyvor\Internal\Auth\Auth;
use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Auth\Oidc\OidcAuth;
use Hyvor\Internal\Billing\Billing;
use Hyvor\Internal\Billing\BillingInterface;
use Hyvor\Internal\InternalConfig;
use Hyvor\Internal\InternalFake;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

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
        $definition->rootNode() // @phpstan-ignore-line
            ->children()
                ->scalarNode('component')->defaultValue('core')->end()
                ->scalarNode('auth_method')->defaultValue('%env(AUTH_METHOD)%')->end()
                ->scalarNode('instance')->defaultValue('%env(HYVOR_INSTANCE)%')->end()
                ->scalarNode('private_instance')->defaultValue('%env(HYVOR_PRIVATE_INSTANCE)%')->end()
                ->booleanNode('fake')->defaultValue('%env(HYVOR_FAKE)%')->end()
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

    /**
     * @param array<mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // SERVICES
        $container->import('../config/services.php');

        // ENV DEFAULTS
        $container->parameters()->set('env(APP_SECRET)', '');
        $container->parameters()->set('env(HYVOR_INSTANCE)', 'https://hyvor.com');
        $container->parameters()->set('env(HYVOR_PRIVATE_INSTANCE)', null);
        $container->parameters()->set('env(HYVOR_FAKE)', '0');
        $container->parameters()->set('env(AUTH_METHOD)', 'hyvor'); // hyvor or openid

        // InternalConfig class
        $container->services()
            ->get(InternalConfig::class)
            ->args([
                '%env(APP_SECRET)%',
                $config['component'],
                $config['auth_method'],
                $config['instance'],
                $config['private_instance'],
                $config['fake'],
                $config['i18n']['folder'],
                $config['i18n']['default'],
            ]);

        // Main Services
        $authMethod = $builder->resolveEnvPlaceholders('%env(AUTH_METHOD)%', true);
        $authInterface = $container->services()->alias(
            AuthInterface::class,
            $authMethod === 'oidc' ? OidcAuth::class : Auth::class
        );
        $billingInterface = $container->services()->alias(BillingInterface::class, Billing::class);

        // sometimes we need to replace services dynamically in services
        // it is only possible for public services
        // @codeCoverageIgnoreStart
        if ($container->env() === 'test') {
            $authInterface->public();
        }
        // @codeCoverageIgnoreEnd

        $isFake = boolval($builder->resolveEnvPlaceholders('%env(HYVOR_FAKE)%', true));
        if ($isFake && $container->env() === 'dev') {
            $this->setupFake($container);
        }
    }

    private function setupFake(ContainerConfigurator $container): void
    {
        $class = class_exists('App\InternalFake') ? 'App\InternalFake' : InternalFake::class;

        /** @var class-string<InternalFake> $class */
        $fakeConfig = new $class;
        $user = $fakeConfig->user();
        $usersDatabase = $fakeConfig->usersDatabase();

        $container
            ->services()
            ->alias(AuthInterface::class, AuthFake::class);

        $container->services()
            ->get(AuthFake::class)
            ->args([
                $user?->toArray(),
                $usersDatabase,
            ]);
    }

}