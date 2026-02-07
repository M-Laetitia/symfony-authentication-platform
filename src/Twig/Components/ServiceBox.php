<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class ServiceBox
{
    public string $title = 'Title';
    public string $subtitle = 'Subtile';
    public string $link = '';
    public string $imageSrc = '';
}
