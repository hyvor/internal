<?php

namespace Hyvor\Internal\Tests\Bundle\Command\Sudo;

use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Auth\Oidc\OidcAuth;
use Hyvor\Internal\Bundle\Command\Sudo\SudoListCommand;
use Hyvor\Internal\Tests\Helper\UpdatesInternalConfig;
use Hyvor\Internal\Tests\SymfonyTestCase;
use Hyvor\Internal\Tests\Unit\Auth\Oidc\OidcUserFactoryTrait;
use Hyvor\Internal\Tests\Unit\Sudo\SudoUserFactoryTrait;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SudoListCommand::class)]
class SudoListCommandTest extends SymfonyTestCase
{

    use SudoUserFactoryTrait;
    use OidcUserFactoryTrait;
    use UpdatesInternalConfig;

    public function test_lists_sudo_users(): void
    {
        AuthFake::enableForSymfony($this->container, usersDatabase: [
            [
                'id' => 1,
                'email' => 'supun@hyvor.com',
                'name' => 'Supun'
            ]
        ]);

        $this->createSudoUser(1, $this->em);

        $command = $this->getCommandTester('sudo:list');
        $command->execute([]);

        $command->assertCommandIsSuccessful();

        $output = $command->getDisplay();
        $this->assertStringContainsString('User ID', $output);
        $this->assertStringContainsString('1', $output);

        $this->assertStringContainsString('Email', $output);
        $this->assertStringContainsString('supun@hyvor.com', $output);

        $this->assertStringContainsString('Name', $output);
        $this->assertStringContainsString('Supun', $output);

        $this->assertStringNotContainsString('OIDC sub', $output);
    }

    public function test_lists_oidc_users(): void
    {
        /** @var OidcAuth $oidcAuth */
        $oidcAuth = $this->container->get(OidcAuth::class);
        $this->container->set(AuthInterface::class, $oidcAuth);

        $oidcUser = $this->createOidcUser(email: 'ishini@hyvor.com', name: 'Ishini', sub: 'ishini-sub', em: $this->em);
        $this->createSudoUser($oidcUser->getId(), $this->em);

        $command = $this->getCommandTester('sudo:list');
        $command->execute([]);

        $command->assertCommandIsSuccessful();

        $output = $command->getDisplay();
        $this->assertStringContainsString('User ID', $output);
        $this->assertStringContainsString((string)$oidcUser->getId(), $output);
        $this->assertStringContainsString('Email', $output);
        $this->assertStringContainsString('ishini@hyvor.com', $output);
        $this->assertStringContainsString('Name', $output);
        $this->assertStringContainsString('Ishini', $output);
        $this->assertStringContainsString('OIDC sub', $output);
        $this->assertStringContainsString('ishini-sub', $output);
    }

}