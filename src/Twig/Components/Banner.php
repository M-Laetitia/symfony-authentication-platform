<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Banner
{
    public string $title = 'Title';
    public ?string $subtile = 'Subtile';
    public ?string $imageSrc = '';
    public ?string $variant ='';
}
