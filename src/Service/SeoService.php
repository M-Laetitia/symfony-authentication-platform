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
            'blog' => 'Explore our latest photography articles, tips, tutorials, and inspirations to enhance your photography skills and creativity.',
            'contact' => 'Get in touch with us for questions, ideas, or support. We\'re here to respond quickly and help you!',
        ];
        $this->metaRobots = [
            'login' => 'noindex, nofollow',
            'register' => 'noindex, nofollow',
            'blog' => 'index, follow',
            'contact' => 'index, follow',
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