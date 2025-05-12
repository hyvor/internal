<?php

namespace Hyvor\Internal\Bundle\Mail\Component;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsTwigComponent(name: 'mail:button', template: '@Internal/components/mail/button.html.twig')]
class Button
{

    // public string $size = 'medium';


    public string $href = '';


}