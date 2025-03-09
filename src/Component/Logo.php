<?php

namespace Hyvor\Internal\Component;

class Logo
{

    public static function dir(): string
    {
        return __DIR__ . '/../../assets/logo';
    }

    public static function svg(Component $component, ?int $size = null): string
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

    public static function url(Component $component): string
    {
        $coreUrl = ComponentUrlResolver::getInstanceUrl();
        return $coreUrl . "/api/public/logo/{$component->value}.svg";
    }

}