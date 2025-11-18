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

    public function __construct(private ?Container $symfonyContainer = null)
    {
    }

    public static function enable(): self
    {
        $self = new self();
        app()->singleton(Resource::class, fn() => $self);
        return $self;
    }

    public static function enableForSymfony(
        Container $container
    ): self {
        $self = new self($container);
        $container->set(Resource::class, $self);
        return $self;
    }

    public function assertRegistered(
        int $userId,
        int $resourceId,
        ?Carbon $at = null
    ): void {
        $resource = $this->getFakeFromContainer();
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

    public function assertDeleted(int $resourceId): void
    {
        $resource = $this->getFakeFromContainer();

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

    private function getFakeFromContainer(): self
    {
        if ($this->symfonyContainer) {
            $fake = $this->symfonyContainer->get(Resource::class);
        } else {
            $fake = app(Resource::class);
        }

        assert($fake instanceof self);
        return $fake;
    }
}
