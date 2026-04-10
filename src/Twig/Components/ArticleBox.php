<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class ArticleBox
{
    public string $title = 'Article title';
    public string $category = 'Category';
    public ?array $tags= null;
    public string $date = '';
    public string $imagePath ='#';
}
