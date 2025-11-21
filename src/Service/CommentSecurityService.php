<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;

class CommentSecurityService
{
    private RateLimiterFactoryInterface $limiter; 
    private LoggerInterface $logger;
    private string $pattern;
    

    public function __construct(RateLimiterFactoryInterface $commentPostingLimiter,LoggerInterface $logger, string $badWordsYamlPath)
    {
        $this->limiter = $commentPostingLimiter;
        $this->logger = $logger;

        // Chargement et normalisation des mots interdits
        $badWords = Yaml::parseFile($badWordsYamlPath)['bad_words'] ?? [];
        $normalizedWords = array_map([$this, 'normalizeText'], $badWords);

        // Précompilation regex
        $escapedWords = array_map('preg_quote', $normalizedWords);
        $this->pattern = '/\b(' . implode('|', $escapedWords) . ')\b/i';
    }

    /**
     * Vérifie si l'user respecte le rate limit.
     * Retourne un objet RateLimit pour gérer les messages.
     */
    public function checkRateLimit(Request $request): array
    {
        $ip = $request->getClientIp();
        $limit = $this->limiter->create($ip)->consume(1);
    
        // OK : le commentaire est accepté
        if ($limit->isAccepted()) {
            return [
                'accepted' => true,
                'retry_after' => null,
                'status' => 'ok'
            ];
        }
    
        // Trop de requêtes >  Rate-limit atteint
        $retryAfter = $limit->getRetryAfter()?->getTimestamp();
    
        // 1. Tentative légèrement excessive >  warning (spam léger)
        if ($limit->getRemainingTokens() > -3) {
            $this->logger->warning(
                "[CommentRateLimit] Excessive usage detected — soft limit reached",
                $this->buildCommonLogContext($request, ['retry_after' => $retryAfter])
            );
    
            return [
                'accepted' => false,
                'retry_after' => $retryAfter,
                'status' => 'excess'
            ];
        }
    
        // 2. Tentatives nombreuses > spam probable
        if ($limit->getRemainingTokens() > -10) {
            $this->logger->error(
                "[CommentRateLimit] Repeated limit violations — probable spam activity", 
                $this->buildCommonLogContext($request, ['retry_after' => $retryAfter])
            );
    
            return [
                'accepted' => false,
                'retry_after' => $retryAfter,
                'status' => 'spam'
            ];
        }
    
        // 3. tentatives massives > bot évident
        $this->logger->critical(
            "[CommentRateLimit] Automated activity detected — bot blocked",
            $this->buildCommonLogContext($request, ['retry_after' => $retryAfter])
        );
    
        return [
            'accepted' => false,
            'retry_after' => $retryAfter,
            'status' => 'bot'
        ];
    }

    /**
     * Vérifie le temps de soumission du formulaire.
     * Retourne un tableau avec le statut et le temps restant.
     */
    public function checkSubmissionTime(int $submittedAt, Request $request,int $minSeconds = 3): array
    {

        // Valeur par défaut pour éviter soumission frauduleuse
        if ($submittedAt === null || $submittedAt === 0) {
            $this->logger->warning(
                '[CommentSubmissionTime] Missing or invalid timestamp — potential tampering attempt',
                $this->buildCommonLogContext($request, ['submitted_at' => $submittedAt])
            );
            
            return [
                'valid' => false,
                'remaining_seconds' => $minSeconds,
                'status' => 'tampered',
                'message' => 'Erreur de validation du formulaire. Veuillez réessayer.',
            ];
        }

        $now = time();
        $timePassed = $now - $submittedAt;
        $isValid = $timePassed >= $minSeconds;


        if (!$isValid) {
            $remaining = $minSeconds - $timePassed;
            
            $this->logger->error(
                '[CommentSubmissionTime] Submission too fast — suspicious automated behavior',
                $this->buildCommonLogContext($request, ['submitted_at' => $submittedAt])
            );

            return [
                'valid' => false,
                'status' => 'too_fast',
                'remaining_seconds' => $remaining,
                'message' => sprintf(
                    'Veuillez attendre encore %d seconde%s avant de poster.',
                    $remaining,
                    $remaining > 1 ? 's' : ''
                ),
            ];
        }

        return [
            'valid' => true,
            'remaining_seconds' => 0,
            'message' => null,
            'status' => 'ok',
        ];
    }

    public function filterCommentContent(string $content): string
    {
        $normalizedContent = $this->normalizeText($content);
        return preg_replace($this->pattern, '****', $normalizedContent);
    }

    private function normalizeText(string $text): string
    {
        return transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0080-\uffff] remove', $text);
    }

    private function buildCommonLogContext(Request $request, array $extra = []): array
    {
        return array_merge([
            'ip' => $request->getClientIp(),
            'route' => $request->attributes->get('_route'),
            'url' => $request->getUri(),
            'method' => $request->getMethod(),
            'user_agent' => $request->headers->get('User-Agent'),
        ], $extra);
    }

    // ajouter honeypot

}