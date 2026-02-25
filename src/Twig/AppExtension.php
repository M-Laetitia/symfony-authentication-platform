<?php
namespace App\Twig;

use App\Repository\MessageRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig Extension pour exposer des fonctions liées aux messages dans les templates.
 * Responsable uniquement de l’interface Twig, délègue la logique métier aux repositories.
 */
class AppExtension extends AbstractExtension
{
    private $messageRepository;
    private $security;

    /**
     * Injection de dépendances via constructeur.
     * @param MessageRepository $messageRepository Accès aux messages en base
     * @param Security $security Service Symfony pour récupérer l’utilisateur connecté
     */
    public function __construct(MessageRepository $messageRepository, Security $security)
    {
        $this->messageRepository = $messageRepository;
        $this->security = $security;
    }

    /**
     * Déclare les fonctions Twig exposées aux templates.
     * 'unread_messages_count' peut être appelée directement depuis Twig.
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('unread_messages_count', [$this, 'getUnreadMessagesCount']),
        ];
    }
    /**
     * Retourne le nombre de messages non lus pour l’utilisateur connecté.
     * - Vérifie que l’utilisateur est authentifié.
     * - Récupère l’ID pour passer au repository.
     * - Délègue la logique de comptage à MessageRepository.
     */
    public function getUnreadMessagesCount(): int
    {
        /** @var \App\Entity\User $user */
        $user = $this->security->getUser(); 
        if (!$user || !is_object($user)) {
            return 0;
        }
    
        $userId = $user->getId();
    
        return $this->messageRepository->countUnreadForUser($userId);
    }
}