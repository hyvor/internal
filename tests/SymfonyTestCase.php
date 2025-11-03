<?php

namespace Hyvor\Internal\Tests;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Zenstruck\Foundry\Test\Factories;

// https://symfonycasts.com/screencast/symfony-bundle/integration-test
class SymfonyTestCase extends TestCase
{

    use Factories;

    public SymfonyKernel $kernel;
    public Container $container;
    public EntityManagerInterface $em;

    protected function getEnv(): string
    {
        return 'test';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = new SymfonyKernel($this->getEnv(), true);
        $kernel->boot();
        $container = $kernel->getContainer();
        assert($container instanceof Container);

        $this->kernel = $kernel;
        /** @var Container $testContainer */
        $testContainer = $container->get('test.service_container');
        $this->container = $testContainer;

        $this->createTables();

        /** @var EntityManagerInterface $em */
        $em = $this->container->get('doctrine.orm.default_entity_manager');
        $this->em = $em;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->kernel->shutdown();
        $this->container->reset();
        $this->em->clear();
    }

    public function createMock(string $originalClassName): MockObject
    {
        return parent::createMock($originalClassName);
    }

    protected function getContainer(): Container
    {
        return $this->container;
    }

    protected function getCommandTester(string $command): CommandTester
    {
        $application = new Application($this->kernel);
        $command = $application->find($command);
        return new CommandTester($command);
    }

    protected function createTables(): void
    {
        $doctrine = $this->container->get('doctrine');
        assert($doctrine instanceof Registry);
        $connection = $doctrine->getConnection();
        assert($connection instanceof Connection);

        $connection->executeQuery('DROP TABLE IF EXISTS oidc_users;');
        $connection->executeQuery('DROP TABLE IF EXISTS sudo_users;');
        $connection->executeQuery(
            <<<SQL
        CREATE TABLE oidc_users (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          created_at  TEXT NOT NULL DEFAULT (datetime('now')),
          updated_at  TEXT NOT NULL DEFAULT (datetime('now')),
          iss         TEXT NOT NULL,
          sub         TEXT NOT NULL,
          email       TEXT NOT NULL,
          name        TEXT NOT NULL,
          picture_url TEXT,
          website_url TEXT,
          UNIQUE (iss, sub)
        );
        SQL
        );
        $connection->executeQuery(
            <<<SQL
        CREATE TABLE sudo_users (
            user_id INTEGER PRIMARY KEY,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL
        );
        SQL
        );
    }

    /**
     * @param MockResponse|MockResponse[] $response
     */
    protected function setHttpClientResponse(MockResponse|array $response): void
    {
        $client = new MockHttpClient($response);
        $this->container->set(HttpClientInterface::class, $client);
    }

}