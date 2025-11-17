<?php

namespace Hyvor\Internal\Component;

class Logo
{

    public function __construct(private InstanceUrlResolver $instanceUrlResolver)
    {
    }

    private static function dir(): string
    {
        return __DIR__ . '/../../assets/logo';
    }

    public static function path(Component $component, bool $png = false): string
    {
        return self::dir() . "/{$component->value}." . ($png ? 'png' : 'svg');
    }

    public static function svg(Component $component, ?int $size = null): string
    {
        $svg = (string)file_get_contents(self::path($component));

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

    public function url(Component $component, bool $png = false): string
    {
        $coreUrl = $this->instanceUrlResolver->publicUrlOfCore();
        return $coreUrl . "/api/public/logo/{$component->value}." . ($png ? 'png' : 'svg');
    }

}