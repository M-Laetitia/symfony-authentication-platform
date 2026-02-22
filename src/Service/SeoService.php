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
            'register' => 'Register page'
        ];
        $this->metaRobots = [
            'login' => 'noindex, nofollow',
            'register' => 'noindex, nofollow',
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