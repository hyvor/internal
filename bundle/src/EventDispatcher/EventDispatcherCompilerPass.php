<?php

namespace Hyvor\Internal\Bundle\EventDispatcher;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EventDispatcherCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $targetIds = ['event_dispatcher', 'debug.event_dispatcher'];
        foreach ($targetIds as $targetId) {
            if ($container->hasDefinition($targetId)) {
                $container->setAlias($targetId, TestEventDispatcher::class)
                    ->setPublic(true);
            }
        }
    }
}