<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class TestimonialBox
{
    public string $rating = '';
    public string $content= '';
    public string $username = '';
    public ?string $subtitle = '';
}
