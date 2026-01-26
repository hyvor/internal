<?php

namespace Hyvor\Internal\Billing\LicenseReflection;

use Hyvor\Internal\Billing\License\License;

class LicenseReflection
{

    /**
     * @param class-string<License> $licenseClass
     * @return LicenseProperty[]
     */
    public function getPropertiesOf(string $licenseClass): array
    {
        $defaults = new $licenseClass();
        $classReflection = new \ReflectionClass($licenseClass);

        $reflectionProperties = $classReflection->getProperties();
        $licenseProperties = [];

        foreach ($reflectionProperties as $reflectionProperty) {
            $type = $reflectionProperty->getType();

            // type should be bool or int
            assert(
                $type instanceof \ReflectionNamedType &&
                $type->isBuiltin() &&
                $type->allowsNull() === false
            );

            $typeName = $type->getName();
            assert($typeName === 'int' || $typeName === 'bool');

            $defaultValue = $reflectionProperty->getValue($defaults);
            assert(is_int($defaultValue) || is_bool($defaultValue));

            $licenseProperties[] = new LicenseProperty(
                $reflectionProperty->getName(),
                $typeName === 'int' ? LicensePropertyType::INT : LicensePropertyType::BOOL,
                $defaultValue,
                $this->cleanDocComment($reflectionProperty->getDocComment())
            );
        }

        return $licenseProperties;
    }

    private function cleanDocComment(false|string $docComment): string
    {
        if (!$docComment) {
            return '';
        }

        // Remove the opening /**, closing */, and leading asterisks/whitespace
        $cleaned = preg_replace('#^\s*/\*\*|\s*\*/$|^\s*\*\s?#m', '', $docComment);

        assert(is_string($cleaned));

        return trim($cleaned);
    }

    /**
     * @template T of License
     * @param class-string<T> $licenseClass
     * @param array<string, int|bool> $data
     * @return License
     */
    public function buildLicenseFromArray(string $licenseClass, array $data, bool $fill = true): License
    {
        $properties = $this->getPropertiesOf($licenseClass);

        // initiate with the defaults
        $license = new $licenseClass;

        foreach ($data as $key => $value) {
            $property = array_find($properties, fn(LicenseProperty $p) => $p->name === $key);

            if (!$property) {
                continue; // this property name is invalid, just skip
            }

            if ($property->type === LicensePropertyType::INT && !is_int($value)) {
                continue; // invalid type, skip
            }

            if ($property->type === LicensePropertyType::BOOL && !is_bool($value)) {
                continue; // invalid type, skip
            }

            // safely set the value
            $license->{$key} = $value;
        }

        return $license;
    }

}
