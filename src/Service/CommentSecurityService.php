<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class CommentSecurityService
{
    private RateLimiterFactory $limiter; // bien pour les tests 

    public function __construct(RateLimiterFactory $commentPostingLimiter)
    {
        $this->limiter = $commentPostingLimiter;
    }

    /**
     * Vérifie si l'user respecte le rate limit.
     * Retourne false si la limite est dépassée.
     */
    public function checkRateLimit(Request $request): bool
    {
        $ip = $request->getClientIp();
        $limit = $this->limiter->create($ip)->consume(1); 

        return $limit->isAccepted();
        //journaliser les dépassement de limite, récupérer l'ip qui spamme souvent, injecter loggerinterface

    }

    /**
     * Vérifie le temps de soumission du formulaire.
     * Retourne false si le délai est trop court.
     */
    public function checkSubmissionTime(int $submittedAt, int $minSeconds = 3): bool
    {
        $now = time();
        return ($now - $submittedAt) >= $minSeconds;
        // retourner l'objet $limit - et afficher le message avec temps d'attente restant - msg ux + précis
    }

    // sanityzecontent

    // honeypot

}