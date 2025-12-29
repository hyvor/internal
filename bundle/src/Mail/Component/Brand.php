<?php

namespace Hyvor\Internal\Bundle\Mail\Component;

use Hyvor\Internal\Component\Component;
use Hyvor\Internal\Component\Logo;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsTwigComponent(name: 'mail:brand', template: '@Internal/components/mail/brand.html.twig')]
class Brand
{

    public string $component = 'core';

    public function __construct(
        private Logo $logo,
    ) {
    }

    private function getComponent(): Component
    {
        return Component::from($this->component);
    }

    #[ExposeInTemplate]
    public function getImage(): string
    {
        return $this->logo->url($this->getComponent(), png: true);
    }

    #[ExposeInTemplate]
    public function getName(): string
    {
        return $this->getComponent()->name();
    }

}