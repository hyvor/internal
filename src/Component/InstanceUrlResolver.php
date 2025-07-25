<?php

namespace Hyvor\Internal\Component;

use Hyvor\Internal\InternalConfig;

class InstanceUrlResolver
{

    public function __construct(private readonly InternalConfig $config)
    {
    }

    /**
     * Get the public URL of a component
     */
    public function publicUrlOf(Component $component): string
    {
        return $this->of($this->config->getInstance(), $component);
    }

    public function publicUrlOfCore(): string
    {
        return $this->of($this->config->getInstance(), Component::CORE);
    }

    public function publicUrlOfCurrent(): string
    {
        return $this->of($this->config->getInstance(), $this->config->getComponent());
    }

    /**
     * Get the private URL of a component, falling back to the public URL if not set
     */
    public function privateUrlOf(Component $component): string
    {
        return $this->of($this->config->getPrivateInstanceWithFallback(), $component);
    }

    public function privateUrlOfCore(): string
    {
        return $this->of($this->config->getPrivateInstanceWithFallback(), Component::CORE);
    }

    public function privateUrlOfCurrent(): string
    {
        return $this->of($this->config->getPrivateInstanceWithFallback(), $this->config->getComponent());
    }

    private function of(string $coreUrl, Component $component): string
    {
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
