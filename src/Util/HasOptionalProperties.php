<?php

namespace Hyvor\Internal\Util;

trait HasOptionalProperties
{

    public function hasProperty(string $property): bool
    {
        try {
            $_ = $this->{$property};
            return true;
        } catch (\Error $e) {
            return false;
        }
    }

}