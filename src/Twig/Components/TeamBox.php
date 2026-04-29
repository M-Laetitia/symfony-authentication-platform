<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class TeamBox
{
    public string $name = '';
    public string $slug = '';
    public string $imagePath = '';
    public string $alt = 'protrait of the photographer';
    public array $tags = [];
    public string $bio = '';

}
