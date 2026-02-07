<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class SectionIntro
{
    public string $title = 'Title';
    public string $subtile = 'Subtile';
    public string $buttonText = 'See more';
    public ?string $variant = null;
    public string $linkBtn ="";
}
