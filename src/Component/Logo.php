<?php

namespace Hyvor\Internal\Component;

use Hyvor\Internal\InternalApi\ComponentType;
use Hyvor\Internal\InternalApi\InstanceUrl;

class Logo
{

    public static function dir(): string
    {
        return __DIR__ . '/../assets/logo';
    }

    public static function svg(ComponentType $component, ?int $size = null): string
    {
        $path = self::dir() . "/{$component->value}.svg";
        $svg = (string)file_get_contents($path);

        if ($size) {
            $svg = (string)preg_replace_callback('/<svg[^>]+/', function ($matches) use ($size) {
                $svgEl = $matches[0];
                $svgEl = (string)preg_replace('/width="[^"]*"/', "width=\"{$size}\"", $svgEl, 1);
                $svgEl = (string)preg_replace('/height="[^"]*"/', "height=\"{$size}\"", $svgEl, 1);
                return $svgEl;
            }, $svg, 1);
        }

        return $svg;
    }

    public static function url(ComponentType $component): string
    {
        $coreUrl = InstanceUrl::getInstanceUrl();
        return $coreUrl . "/api/public/logo/{$component->value}.svg";
    }

}