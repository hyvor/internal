<?php

namespace Hyvor\Internal\Util\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonType;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Usage:
 *
 * class BlogMetaDoctrineType extends CustomJsonType
 * {
 *     public function getTypeName(): string
 * }
 *
 * Add to doctrine.yaml:
 *
 * doctrine:
 *    dbal:
 *      types:
 *          blogs_meta: App\Entity\Meta\BlogMetaDoctrineType
 *
 * Update your entity:
 * #[ORM\Column(type: 'blogs_meta', nullable: true)]
 */
abstract class CustomJsonType extends JsonType
{

    /**
     * @return class-string
     */
    abstract public function getTypeName(): string;

    /**
     * depending on the framework's serializer is a big hard (need to get it DI'd, which Doctrine doesn't natively support)
     * Tried with event listeners, but felt too hacky
     * So, we create a custom serializer here that supports objects with enums
     */
    public function createSerializer(): Serializer
    {
        $reflectionExtractor = new ReflectionExtractor();

        $propertyInfo = new PropertyInfoExtractor(
            listExtractors: [$reflectionExtractor],
            typeExtractors: [$reflectionExtractor],
            accessExtractors: [$reflectionExtractor],
        );

        $objectNormalizer = new ObjectNormalizer(
            propertyAccessor: new PropertyAccessor(),
            propertyTypeExtractor: $propertyInfo,
        );

        return new Serializer(
            normalizers: [
                new BackedEnumNormalizer(),
                $objectNormalizer,
            ],
            encoders: [new JsonEncoder()],
        );

    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?object
    {
        if ($value === null) {
            return null;
        }
        return $this->createSerializer()->deserialize($value, $this->getTypeName(), 'json');
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }
        return $this->createSerializer()->serialize($value, 'json');
    }

}
