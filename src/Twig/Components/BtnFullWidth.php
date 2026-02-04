<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class BtnFullWidth
{
    public string $label = 'Button';
    public ?string $href = null;
    public string $variant = 'primary';

}
