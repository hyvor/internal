<?php

namespace Hyvor\Internal\Billing\License;

use Hyvor\Internal\Billing\License\Property\LicenseProperty;
use Hyvor\Internal\Billing\License\Property\LicensePropertyType;
use Hyvor\Internal\Util\Transfer\Serializable;

/**
 * Add license parameters in the constructor.
 * When creating a license, set the limits to that of the trial license.
 * ONLY USE int and bool types in the constructor.
 *
 * int: use the smallest possible type (bytes instead of kb, gb)
 * int: use -1 for Infinity
 */
abstract class License
{
    use Serializable;

    /**
     * @return LicenseProperty[]
     */
    public static function properties(): array
    {
        return [];
    }

    abstract public static function trial(): static;

    /**
     * @param array<mixed> $data
     */
    public static function fromArray(array $data, bool $fill = true): static
    {
        $properties = static::properties();

        // create license object without the constructor
        $license = new \ReflectionClass(static::class)->newInstanceWithoutConstructor();

        // fill all properties with default values
        foreach ($properties as $property) {
            // 0 or false
            $license->{$property->key} = $property->type->default();
        }

        // extend with given data
        foreach ($data as $key => $value) {
            $property = array_find($properties, fn(LicenseProperty $p) => $p->key === $key);

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
