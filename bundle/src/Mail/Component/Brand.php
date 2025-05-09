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

    private function getComponent(): Component
    {
        return Component::from($this->component);
    }

    #[ExposeInTemplate]
    public function getImage(): string
    {
        $svg = Logo::svg($this->getComponent());
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    #[ExposeInTemplate]
    public function getName(): string
    {
        return $this->getComponent()->name();
    }

}