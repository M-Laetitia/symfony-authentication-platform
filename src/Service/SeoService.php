<?php

namespace App\Service;

class SeoService
{
    private array $metaDescriptions;
    private array $metaRobots;

    public function __construct()
    {
        $this->metaDescriptions = [
            'login' => 'Login page',
            'register' => 'Register page',
            'blog' => 'Explore photography tips, techniques, and artistic insights to elevate your art. Discover creative inspiration on our blog.',
            'contact' => 'Get in touch with us for questions, ideas, or support. We\'re here to respond quickly and help you!',
            'home' => 'Welcome to our photography blog, where we share tips, techniques, and inspiration to help you capture stunning images. Explore our latest articles and unleash your creativity.',
        ];
        $this->metaRobots = [
            'login' => 'noindex, nofollow',
            'register' => 'noindex, nofollow',
            'blog' => 'index, follow',
            'contact' => 'index, follow',
            'home' => 'index, follow',
        ];
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