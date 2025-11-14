<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class CommentSecurityService
{
    private RateLimiterFactory $limiter; // bien pour les tests 
    private LoggerInterface $logger;

    public function __construct(RateLimiterFactory $commentPostingLimiter,LoggerInterface $logger)
    {
        $this->limiter = $commentPostingLimiter;
        $this->logger = $logger;
        // $this->logger->emergency('TEST LOG'); 
        // dd($this->logger->getHandlers());
        
    }

    /**
     * Vérifie si l'user respecte le rate limit.
     * Retourne un objet RateLimit pour gérer les messages.
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
     * Retourne un tableau avec le statut et le temps restant.
     */
    public function checkSubmissionTime(?int $submittedAt, int $minSeconds = 3): array
    {
        // $now = time();
        // return ($now - $submittedAt) >= $minSeconds;
        $this->logger->error('TEST DE LOG MANUEL', ['test' => true]);
        // Valeur par défaut pour éviter soumission frauduleuse
        if ($submittedAt === null || $submittedAt === 0) {
            $this->logger->warning('Tentative de soumission sans timestamp', [
                'submitted_at' => $submittedAt,
            ]);
            
            return [
                'valid' => false,
                'remaining_seconds' => $minSeconds,
                'message' => 'Erreur de validation du formulaire. Veuillez réessayer.',
            ];
        }

        $now = time();
        $timePassed = $now - $submittedAt;
        $isValid = $timePassed >= $minSeconds;


        if (!$isValid) {
            $remaining = $minSeconds - $timePassed;
            
            $this->logger->error('Soumission trop rapide', [
                'timePassed' => $timePassed,
                'min_required' => $minSeconds,
                'remaining' => $remaining,
            ]);

            return [
                'valid' => false,
                'remaining_seconds' => $remaining,
                'message' => sprintf(
                    'Veuillez attendre encore %d seconde%s avant de poster.',
                    $remaining,
                    $remaining > 1 ? 's' : ''
                ),
            ];
        }

        file_put_contents('test-direct.txt', "OK\n", FILE_APPEND);
        return [
            'valid' => true,
            'remaining_seconds' => 0,
            'message' => null,
        ];
    }

    // a ajouter
    // sanityzecontent

    // honeypot

}