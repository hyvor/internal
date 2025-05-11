<?php

namespace Hyvor\Internal\Laravel;

use Illuminate\Foundation\Application;

class Laravel
{

    public static function isLaravel(): bool
    {
        return function_exists('app') && app() instanceof Application;
    }

}