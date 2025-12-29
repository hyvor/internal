<?php

namespace Hyvor\Internal\Tests\Unit\Auth;

use Hyvor\Internal\Auth\Auth;
use Hyvor\Internal\Auth\AuthFactory;
use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Auth\Oidc\OidcAuth;
use Hyvor\Internal\Tests\SymfonyTestCase;

class AuthFactoryTest extends SymfonyTestCase
{

    public function test_create_hyvor_fake(): void
    {
        $_ENV['HYVOR_FAKE'] = '1';
        $_ENV['AUTH_METHOD'] = 'hyvor';

        /** @var AuthFactory $factory */
        $factory = $this->container->get(AuthFactory::class);
        $auth = $factory->create();

        $this->assertInstanceOf(AuthFake::class, $auth);

        unset($_ENV['HYVOR_FAKE']);
        unset($_ENV['AUTH_METHOD']);
    }

    public function test_create_hyvor(): void
    {
        $_ENV['AUTH_METHOD'] = 'hyvor';

        /** @var AuthFactory $factory */
        $factory = $this->container->get(AuthFactory::class);
        $auth = $factory->create();

        $this->assertInstanceOf(Auth::class, $auth);

        unset($_ENV['AUTH_METHOD']);
    }

    public function test_create_oidc(): void
    {
        $_ENV['AUTH_METHOD'] = 'oidc';

        /** @var AuthFactory $factory */
        $factory = $this->container->get(AuthFactory::class);
        $auth = $factory->create();

        $this->assertInstanceOf(OidcAuth::class, $auth);

        unset($_ENV['AUTH_METHOD']);
    }

}