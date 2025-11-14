<?php

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * HoneyPotSubscriber
 *
 * Ce subscriber implémente une protection anti-bot en utilisant un champ honeypot.
 * Il intervient lors de l’événement PRE_SUBMIT afin d’évaluer si la soumission 
 * d’un formulaire peut provenir d’un robot (remplissage automatique de champs cachés).
 *
 * Objectifs :
 *  - Détecter les bots passifs sans impacter l’expérience utilisateur
 *  - Centraliser la logique de sécurité liée au honeypot (principe SRP)
 *  - Générer une traçabilité complète via logs (IP, User-Agent, requête HTTP)
 *
 * Notes d’architecture :
 *  - S’appuie sur des principes de sécurité applicative (ex. : détection passive automatisée)
 *  - Découplage propre grâce à EventSubscriber (architecture modulaire)
 *  - Facilite la maintenance : la logique anti-bot est isolée et testable
*/

class HoneyPotSubscriber implements EventSubscriberInterface
{
    /** Logger dédié permettant d’isoler les logs de sécurité (traçabilité des bots détectés). */
    private LoggerInterface $honeyPotLogger;
    /** Permet d'accéder à la requête courante afin d'extraire les informations réseau (IP, méthode HTTP...). */
    private RequestStack $requestStack;

    /**
     * @param LoggerInterface $honeyPotLogger Logger spécialisé pour la détection honeypot
     * @param RequestStack    $requestStack   Accès aux informations HTTP de la requête active
    */

    public function __construct(LoggerInterface $honeyPotLogger, RequestStack $requestStack)
    {
        $this->honeyPotLogger = $honeyPotLogger;
        $this->requestStack = $requestStack;
    }
    
    /**
     * Déclaration des événements écoutés.
     *
     * Le subscriber écoute PRE_SUBMIT afin d’intervenir *avant* le mapping des données,
     * ce qui permet de stopper la soumission du formulaire avant tout traitement applicatif.
     *
     * @return array<string, string>
    */

    public static function getSubscribedEvents(): array {

        //^ lors du subscribe - l'event pre_submit est fired > on appelle en callback checkHoneyJar > checkHoneyJar prend un formEvents > dans ce form event on récupère les datas qui ont été saisies
        return [
            FormEvents::PRE_SUBMIT => 'checkHoneyJar'
        ];
    }

    /**
     * Vérifie si les champs honeypot ont été remplis.
     *
     * Les bots ayant tendance à remplir tous les champs visibles dans le DOM,
     * la présence d’une valeur dans “phone” ou “faxNumber” est considérée comme suspecte.
     *
     * En cas de détection :
     *  - journalisation d’un log de sécurité
     *  - génération d’une réponse HTTP 403 pour bloquer la soumission
     *
     * @param FormEvent $event Données envoyées par l’utilisateur (ou le bot) avant traitement
     *
     * @throws HttpException Si le formulaire semble avoir été manipulé ou rempli automatiquement
    */

    public function checkHoneyJar(FormEvent $event): void{

        // Récupération des données brutes avant mappage dans l’objet du formulaire
        $request = $this->requestStack->getCurrentRequest();
        $data = $event->getData();

        // Contrôle d’intégrité : les champs honeypot doivent exister
        // Permet d'éviter les attaques supprimant volontairement les champs
        if(!array_key_exists('phone', $data) || !array_key_exists('faxNumber', $data)) {
            throw new HttpException(400, 'Requête invalide.');
        }

        // Extraction explicite pour améliorer la lisibilité
        [
            'phone'     => $phone,
            'faxNumber' => $faxNumber
        ] = $data;

        
        // Détection du bot
        if($phone !== '' || $faxNumber !== '') {
            // Journalisation détaillée de l’incident (sécurité / audit / traçabilité) - niveau warning
            $this->honeyPotLogger->warning("Suspicion de bot détectée", [
                'ip' => $request->getClientIp(),
                'http_info' => sprintf('%s %s %s %d', 
                    $request->getMethod(),
                    $request->getRequestUri(),
                    $request->getProtocolVersion(),
                    403
                )
            ]);
                
            // Log additionnel montrant le contenu envoyé par le bot
            $this->honeyPotLogger->info("Valeurs envoyées dans les champs honeypot : phone='{$phone}', faxNumber='{$faxNumber}'", [
                'ip' => $request->getClientIp(),
                'user_agent' => $request->headers->get('User-Agent') ?? 'unknown',
                'route' => $request->attributes->get('_route') ?? 'no_route',
                'method' => $request->getMethod(),
            ]);

            // Interruption immédiate de la requête (fail-safe)
            throw new HttpException(403, 'Requête non autorisée.');
        }

    }
}