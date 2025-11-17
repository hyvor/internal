<?php

namespace Hyvor\Internal\Resource;

use Carbon\Carbon;
use Symfony\Component\DependencyInjection\Container;

final class ResourceFake extends Resource
{

    /** @var array<array{userId: int, resourceId: int, at: ?Carbon}> */
    private array $registered = [];

    /** @var array<int> */
    private array $deleted = [];

    private static ?Container $symfonyContainer = null;

    public function __construct()
    {
    }

    public function __destruct()
    {
        self::$symfonyContainer = null;
    }

    public static function enable(): void
    {
        app()->singleton(Resource::class, function () {
            return new self();
        });
    }

    public static function enableForSymfony(
        Container $container
    ): void {
        self::$symfonyContainer = $container;
        $container->set(Resource::class, new self());
    }

    public static function assertRegistered(
        int $userId,
        int $resourceId,
        ?Carbon $at = null
    ): void {
        $resource = self::getFakeFromContainer();
        $registered = false;

        foreach ($resource->registered as $registered) {
            if (
                $registered['userId'] === $userId &&
                $registered['resourceId'] === $resourceId &&
                ($at === null || $registered['at']?->getTimestamp() === $at->getTimestamp())
            ) {
                $registered = true;
                break;
            }
        }

        \PHPUnit\Framework\Assert::assertTrue(
            $registered,
            "Resource not registered for user $userId and resource $resourceId" . ($at ? " at $at" : '')
        );
    }

    public static function assertDeleted(int $resourceId): void
    {
        $resource = self::getFakeFromContainer();

        \PHPUnit\Framework\Assert::assertContains(
            $resourceId,
            $resource->deleted,
            "Resource not deleted: $resourceId"
        );
    }

    public function register(
        int $userId,
        int $resourceId,
        ?Carbon $at = null
    ): void {
        $this->registered[] = [
            'userId' => $userId,
            'resourceId' => $resourceId,
            'at' => $at,
        ];
    }

    public function delete(int $resourceId): void
    {
        $this->deleted[] = $resourceId;
    }

    private static function getFakeFromContainer(): self
    {
        if (self::$symfonyContainer) {
            $fake = self::$symfonyContainer->get(Resource::class);
        } else {
            $fake = app(Resource::class);
        }

        assert($fake instanceof self);
        return $fake;
    }
}
