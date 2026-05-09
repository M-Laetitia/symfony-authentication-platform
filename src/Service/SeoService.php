<?php

namespace App\Service;

class SeoService
{
    private array $metaTitles;
    private array $metaDescriptions;
    private array $metaRobots;

    public function __construct()
    {
        $this->metaTitles = [
            'home'     => 'Photography Agency & Creative Photographers | MOSAIC',
            'blog'     => 'Photography Blog, Tips & Inspiration | MOSAIC',
            'contact'  => 'Contact Our Photography Agency | MOSAIC',
            'team'     => 'Our Professional Photographers | MOSAIC',
            'gallery'  => 'Photography Portfolio & Gallery | MOSAIC',
            'login'    => 'Client Login | MOSAIC',
            'register' => 'Create Account | MOSAIC',
        ];
        $this->metaDescriptions = [
            'login' => 'Login page',
            'register' => 'Register page',
            'blog' => 'Explore photography tips, techniques, and artistic insights to elevate your art. Discover creative inspiration on our blog.',
            'contact' => 'Get in touch with us for questions, ideas, or support. We\'re here to respond quickly and help you!',
            'home' => 'Professional photography agency capturing emotion, light, and timeless moments. Discover our visual storytelling, expertise, and passion for creating stunning, meaningful images.',
            'team' => 'Meet our talented photographers, each with a unique style and vision. Discover their passion for storytelling through the lens and explore their captivating portfolios.',
            'gallery' => 'Explore our gallery — portraits, weddings, fashion editorials and documentary photography. Discover the work of our photographers based in Strasbourg.',
        ];
        $this->metaRobots = [
            'login' => 'noindex, nofollow',
            'register' => 'noindex, nofollow',
            'chat' => 'noindex, nofollow',
            'blog' => 'index, follow',
            'contact' => 'index, follow',
            'home' => 'index, follow',
            'team' => 'index, follow',
            'gallery' => 'index, follow',
        ];
    }

    public function getMetaTitle(string $page): string
    {
        return $this->metaTitles[$page] ?? 'MOSAIC';
    }

    /**
     * Return metadescription for pages
     */
    public function getMetaDescription(string $page): string
    {
        return $this->metaDescriptions[$page] ?? 'Default meta description for the site';
    }
    public function getMetaRobots(string $page): string
    {
        return $this->metaRobots[$page] ?? 'index, follow';
    }

}