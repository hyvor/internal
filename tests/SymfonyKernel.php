<?php

namespace Hyvor\Internal\Tests;

use Hyvor\Internal\Bundle\InternalBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class SymfonyKernel extends Kernel
{


    public function registerBundles(): iterable
    {
        return [
            //
            new SymfonyTestBundle(),

            new InternalBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
    }
}