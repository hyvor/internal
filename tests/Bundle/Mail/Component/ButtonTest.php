<?php

namespace Hyvor\Internal\Tests\Bundle\Mail\Component;

use Hyvor\Internal\Bundle\Mail\Component\Button;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Button::class)]
class ButtonTest extends TestCase
{

    public function test_button(): void
    {
        $button = new Button();
        $button->href = 'https://example.com';
        $this->assertSame('https://example.com', $button->href);
    }

}