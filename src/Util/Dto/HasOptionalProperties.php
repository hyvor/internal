<?php

namespace Hyvor\Internal\Util\Dto;

trait HasOptionalProperties
{

    /**
     * The hackiest PHP code I have written :)
     * checks if a property is set on the current object
     * this property must not have a default value, otherwise it will always return true
     * type safety via PHPStan extension
     */
    public function hasProperty(string $property): bool
    {
        try {
            $value = $this->{$property};
            return true;
        } catch (\Error $e) {
            return false;
        }
    }

}
