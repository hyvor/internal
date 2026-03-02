<?php

namespace Hyvor\Internal\Tests\Bundle\Api\SudoObject;

use Hyvor\Internal\Bundle\Api\SudoObject\SudoObjectFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SudoObjectFactory::class)]
class SudoObjectFactoryTest extends TestCase
{
    private SudoObjectFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new SudoObjectFactory();
    }

    public function test_basic_column_properties(): void
    {
        $entity = new \App\Entity\SimpleEntity('hello', 42, null);
        $result = $this->factory->create($entity);

        $this->assertSame([
            'name' => 'hello',
            'count' => 42,
            'nullable' => null,
        ], $result);
    }

    public function test_non_column_properties_are_excluded(): void
    {
        $entity = new \App\Entity\SimpleEntity('hello', 42, null);
        $result = $this->factory->create($entity);

        $this->assertArrayNotHasKey('excluded', $result);
    }

    public function test_datetime_immutable_is_converted_to_timestamp(): void
    {
        $dt = new \DateTimeImmutable('@1700000000');
        $entity = new \App\Entity\EntityWithDatetime($dt);
        $result = $this->factory->create($entity);

        $this->assertSame(1700000000, $result['created_at']);
    }

    public function test_backed_enum_is_converted_to_value(): void
    {
        $entity = new \App\Entity\EntityWithEnum(\App\Enum\SudoFactoryStatus::Active);
        $result = $this->factory->create($entity);

        $this->assertSame('active', $result['status']);
    }

    public function test_one_to_many_relationship_included_when_specified(): void
    {
        $child1 = new \App\Entity\ChildEntity('first');
        $child2 = new \App\Entity\ChildEntity('second');
        $parent = new \App\Entity\ParentEntity('parent', [$child1, $child2]);

        $result = $this->factory->create($parent, [
            \App\Entity\ParentEntity::class => ['children'],
        ]);

        $this->assertSame('parent', $result['name']);
        $this->assertCount(2, $result['children']);
        $this->assertSame(['name' => 'first'], $result['children'][0]);
        $this->assertSame(['name' => 'second'], $result['children'][1]);
    }

    public function test_many_to_one_relationship_included_when_specified(): void
    {
        $parent = new \App\Entity\ParentEntity('parent', []);
        $child = new \App\Entity\ChildEntity('child', $parent);

        $result = $this->factory->create($child, [
            \App\Entity\ChildEntity::class => ['parent'],
        ]);

        $this->assertSame('child', $result['name']);
        $this->assertSame(['name' => 'parent'], $result['parent']);
    }

    public function test_many_to_one_null_relationship(): void
    {
        $child = new \App\Entity\ChildEntity('child', null);

        $result = $this->factory->create($child, [
            \App\Entity\ChildEntity::class => ['parent'],
        ]);

        $this->assertNull($result['parent']);
    }

    public function test_relationship_not_included_when_not_specified(): void
    {
        $parent = new \App\Entity\ParentEntity('parent', []);

        $result = $this->factory->create($parent);

        $this->assertArrayNotHasKey('children', $result);
    }

    public function test_logic_exception_for_unmapped_relationship_property(): void
    {
        $entity = new \App\Entity\EntityWithUnmappedRelationship();

        $this->expectException(\LogicException::class);

        $this->factory->create($entity, [
            \App\Entity\EntityWithUnmappedRelationship::class => ['other'],
        ]);
    }
}


// Helpers

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class SimpleEntity
{
    #[ORM\Column]
    public string $name;

    #[ORM\Column]
    public int $count;

    #[ORM\Column]
    public ?string $nullable;

    public string $excluded = 'excluded';

    public function __construct(string $name, int $count, ?string $nullable)
    {
        $this->name = $name;
        $this->count = $count;
        $this->nullable = $nullable;
    }
}

#[ORM\Entity]
class EntityWithDatetime
{
    #[ORM\Column]
    public \DateTimeImmutable $created_at;

    public function __construct(\DateTimeImmutable $created_at)
    {
        $this->created_at = $created_at;
    }
}

#[ORM\Entity]
class EntityWithEnum
{
    #[ORM\Column]
    public \App\Enum\SudoFactoryStatus $status;

    public function __construct(\App\Enum\SudoFactoryStatus $status)
    {
        $this->status = $status;
    }
}

#[ORM\Entity]
class ParentEntity
{
    #[ORM\Column]
    public string $name;

    /**
     * @var Collection<int, ChildEntity>
     */
    #[ORM\OneToMany(targetEntity: ChildEntity::class, mappedBy: 'parent')]
    public Collection $children;

    /**
     * @param string $name
     * @param ChildEntity[] $children
     */
    public function __construct(string $name, array $children)
    {
        $this->name = $name;
        $this->children = new ArrayCollection($children);
    }
}

#[ORM\Entity]
class ChildEntity
{
    #[ORM\Column]
    public string $name;

    #[ORM\ManyToOne(targetEntity: ParentEntity::class)]
    public ?ParentEntity $parent;

    public function __construct(string $name, ?ParentEntity $parent = null)
    {
        $this->name = $name;
        $this->parent = $parent;
    }
}

#[ORM\Entity]
class EntityWithUnmappedRelationship
{
    #[ORM\Column]
    public string $name = 'test';

    public string $other = 'other';
}


namespace App\Enum;

enum SudoFactoryStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
