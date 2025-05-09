<?php

namespace Hyvor\Internal\Bundle\Mail\Component;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsTwigComponent(name: 'mail:button', template: '@Internal/components/mail/button.html.twig')]
class Button
{

    /**
     * @var 'small' | 'medium' | 'large'
     */
    public string $size = 'medium';
    public string $href = '';

    #[ExposeInTemplate]
    public function getStyles(): string
    {
        $styles = [
            'small' => 'padding: 2px 12px; height: 26px; font-size: 13px;',
            'medium' => 'padding: 2px 14px; height: 30px; font-size: 14px;',
            'large' => 'padding: 2px 20px; height: 36px; font-size: 14px;',
        ];
        return $styles[$this->size];
    }


}