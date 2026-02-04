<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

interface HoneyPotCheckerInterface
{
    /**
     * Vérifie les champs honeypot depuis un tableau de données.
     */
    public function checkHoneyJar(array &$data, Request $request, array $options): void;
}