<?php

namespace Hyvor\Internal\Tests;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

// https://symfonycasts.com/screencast/symfony-bundle/integration-test
class SymfonyTestCase extends TestCase
{

    public SymfonyKernel $kernel;
    public Container $container;
    public EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = new SymfonyKernel('test', true);
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

    public function createMock(string $originalClassName): MockObject
    {
        return parent::createMock($originalClassName);
    }

    protected function getContainer(): Container
    {
        return $this->container;
    }

    protected function createTables(): void
    {
        $doctrine = $this->container->get('doctrine');
        assert($doctrine instanceof Registry);
        $connection = $doctrine->getConnection();
        assert($connection instanceof Connection);

        $connection->executeQuery('DROP TABLE IF EXISTS oidc_users;');
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