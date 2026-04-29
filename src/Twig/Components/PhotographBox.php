<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class PhotographBox
{
    public string $name = 'name';
    public string $service = 'service';
    public string $imgSrc = '';
    public string $linkBtn ="";
    public string $slug = 'home';
}
