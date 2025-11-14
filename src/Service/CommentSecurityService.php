<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
// use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

class CommentSecurityService
{
    private RateLimiterFactoryInterface $limiter; // bien pour les tests 
    private LoggerInterface $logger;

    public function __construct(RateLimiterFactoryInterface $commentPostingLimiter,LoggerInterface $logger)
    {
        $this->limiter = $commentPostingLimiter;
        $this->logger = $logger;
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
    public function checkSubmissionTime(
        int $submittedAt,
        Request $request,
        int $minSeconds = 60
    ): array
    {
        // $now = time();
        // return ($now - $submittedAt) >= $minSeconds;
        // dd($this->logger->getHandlers());
        // dd($this->logger->getName()); 
        // dd('ON EST BIEN DANS checkSubmissionTime()');
        // $this->logger->error('TEST DE LOG MANUEL');
        $this->logger->error("DEBUT DE LA METHODE", ['submittedAt' => $submittedAt]);
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
            
            $this->logger->error('Soumission d\'un commentaire trop rapide - suspicion de bot ', [
                'ip' => $request->getClientIp(),          // IP du client
                'user_agent' => $request->headers->get('User-Agent'),  // Navigateur/robot
                'url' => $request->getUri(),              // URL demandée
                'route' => $request->attributes->get('_route'),  // Route Symfony
                'method' => $request->getMethod(),        // Méthode HTTP (GET/POST)
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

        // file_put_contents('test-direct.txt', "OK\n", FILE_APPEND);
        return [
            'valid' => true,
            'remaining_seconds' => 0,
            'message' => null,
        ];
        $this->logger->error("FIN DE LA METHODE");
    }

    // a ajouter
    // sanityzecontent

    // honeypot

}