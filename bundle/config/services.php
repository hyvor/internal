<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->autowire(true)
        ->autoconfigure(true);

    // load all files as services
    $services
        ->load('Hyvor\\Internal\\Bundle\\', '../src')
        ->exclude([
            '../src/Comms/Message',
            '../src/Testing'
        ]);

    $internalServices = $services
        ->load('Hyvor\\Internal\\', '../../src')
        ->exclude([
            '../../src/config.php',
            '../../src/Auth/AuthCurrentOrganization.php',
            '../../src/Auth/AuthUser.php',
            '../../src/Billing/Dto',
            '../../src/Billing/License',
            '../../src/Billing/Usage',
            '../../src/Metric',
            '../../src/Http',
            '../../src/routes',
            '../../src/InternalServiceProvider.php'
        ]);

    if ($container->env() === 'test') {
        $internalServices->public();

        // Unreferenced service used in tests
        $services->set(\Hyvor\Internal\Bundle\Mail\Component\Brand::class)->public();
    }

//    $services->load('Hyvor\\Internal\\Auth\\', '../../src/Auth');
//    $services->load('Hyvor\\Internal\\InternalApi\\', '../../src/InternalApi');
//    $services->load('Hyvor\\Internal\\Util\\', '../../src/Util');
};