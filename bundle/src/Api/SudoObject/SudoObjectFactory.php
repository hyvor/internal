<?php

namespace Hyvor\Internal\Bundle\Api\SudoObject;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Generates an array from an entity
 *
 * includes all columns
 * one-to-many and many-to-one relationships are ignored by default
 * but, they can be included using the $relationships attribute
 * ex:
 * [
 *    EnterpriseContract::class => ['order_forms'],
 * ]
 */
class SudoObjectFactory
{

    /**
     * @param array<class-string, string[]> $relationships
     * @return array<mixed>
     */
    public function create(
        object $entity,
        array $relationships = []
    ): array {
        assert(str_starts_with(get_class($entity), 'App\\Entity\\'));

        $return = [];

        $relationshipsToInclude = $relationships[get_class($entity)] ?? [];
        $reflectionClass = new \ReflectionClass($entity);
        $properties = $reflectionClass->getProperties();

        foreach ($properties as $property) {
            $column = $this->getAttribute($property, ORM\Column::class);

            if ($column) {
                $return[$property->getName()] = $this->getPropertyValue($entity, $property);
                continue;
            }

            // to continue, it must be a relationship that is explicitly included
            if (!in_array($property->getName(), $relationshipsToInclude)) {
                continue;
            }

            $oneToMany = $this->getAttribute($property, ORM\OneToMany::class);
            $manyToOne = $this->getAttribute($property, ORM\ManyToOne::class);

            if ($oneToMany) {
                /** @var Collection<int, object> $manyEntities */
                $manyEntities = $property->getValue($entity);
                $manyEntitiesArray = [];

                foreach ($manyEntities as $manyEntity) {
                    $manyEntitiesArray[] = $this->create($manyEntity, $relationships);
                }

                $return[$property->getName()] = $manyEntitiesArray;
                continue;
            }

            if ($manyToOne) {
                $parentEntity = $property->getValue($entity);

                if  ($parentEntity === null) {
                    $return[$property->getName()] = null;
                    continue;
                }

                assert(is_object($parentEntity));
                $return[$property->getName()] = $this->create($parentEntity, $relationships);
                continue;
            }

            throw new \LogicException('should not reach here');
        }

        return $return;
    }

    private function getPropertyValue(object $entity, \ReflectionProperty $property): mixed
    {
        $rawValue = $property->getValue($entity);

        if ($rawValue instanceof \DateTimeImmutable) {
            return $rawValue->getTimestamp();
        }

        if ($rawValue instanceof \BackedEnum) {
            return $rawValue->value;
        }

        return $rawValue;
    }

    /**
     * @template T of object
     * @param class-string<T> $attributeClass
     * @return ?\ReflectionAttribute<T>
     */
    private function getAttribute(\ReflectionProperty $property, string $attributeClass): ?object
    {
        $attrs = $property->getAttributes($attributeClass);

        if (count($attrs) === 0) {
            return null;
        }

        assert(count($attrs) === 1, 'more than one expected attributes of ' . $attributeClass);

        /** @var \ReflectionAttribute<T> $attr */
        $attr = $attrs[0];

        return $attr;
    }

}
