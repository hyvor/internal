<?php

namespace Hyvor\Internal\Component;

class ComponentUrlResolver
{

    public function __construct(private readonly string $coreUrl)
    {
    }

    public function of(Component $component): string
    {
        $coreUrl = $this->coreUrl;

        if ($component === Component::CORE) {
            return $coreUrl;
        } else {
            $subdomain = $component->value;

            $coreHost = parse_url($coreUrl, PHP_URL_HOST);
            $protocol = parse_url($coreUrl, PHP_URL_SCHEME) . '://';

            return $protocol . $subdomain . '.' . $coreHost;
        }
    }


}
