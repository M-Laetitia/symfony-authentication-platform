<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class ArticleBlogBox
{
    public string $title = 'Article title';
    public string $excerpt = '';
    public ?array $tags= null;
    public string $date = '';
    public string $imagePath ='#';
    public string $slug = '';
    public string $alt = 'Article image';
}
