<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class IconBox
{
    public string $variant = 'dark'; 
    public string $svg; 
}
