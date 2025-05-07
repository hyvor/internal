<?php

namespace Hyvor\Internal\Bundle\Mail;

use Twig\Environment;

class MailTemplate
{


    public function __construct(private Environment $twig)
    {
    }

    public function render(): string
    {
        return $this->twig->render('@HyvorInternal/mail/mail.twig');
    }
}