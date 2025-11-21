<?php

namespace Hyvor\Internal\Resource;

use Carbon\Carbon;
use PHPUnit\Framework\Assert;
use Symfony\Component\DependencyInjection\Container;

final class ResourceFake implements ResourceInterface
{

    /** @var array<array{organizationId: int, resourceId: int, at: ?Carbon}> */
    private array $registered = [];

    /** @var array<int> */
    private array $deleted = [];

    public function __construct()
    {
    }

    public static function enable(): self
    {
        $fake = new self();
        app()->singleton(Resource::class, fn() => $fake);
        return $fake;
    }

    public static function enableForSymfony(Container $container): self
    {
        $fake = new self();
        $container->set(ResourceInterface::class, $fake);
        return $fake;
    }

    public function assertRegistered(
        int $organizationId,
        int $resourceId,
        ?Carbon $at = null
    ): void {
        $registered = false;

        foreach ($this->registered as $registered) {
            if (
                $registered['organizationId'] === $organizationId &&
                $registered['resourceId'] === $resourceId &&
                ($at === null || $registered['at']?->getTimestamp() === $at->getTimestamp())
            ) {
                $registered = true;
                break;
            }
        }

        Assert::assertTrue(
            $registered,
            "Resource not registered for user $organizationId and resource $resourceId" . ($at ? " at $at" : '')
        );
    }

    public function assertDeleted(int $resourceId): void
    {
        Assert::assertContains(
            $resourceId,
            $this->deleted,
            "Resource not deleted: $resourceId"
        );
    }

    public function register(
        int $organizationId,
        int $resourceId,
        ?Carbon $at = null
    ): void {
        $this->registered[] = [
            'organizationId' => $organizationId,
            'resourceId' => $resourceId,
            'at' => $at,
        ];
    }

    public function delete(int $resourceId): void
    {
        $this->deleted[] = $resourceId;
    }

}
