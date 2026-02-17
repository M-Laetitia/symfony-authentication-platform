<?php 
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutFlashSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => 'onLogout',
        ];
    }

    public function onLogout(LogoutEvent $event): void
    {
        // Récupère la session depuis la requête
        $session = $event->getRequest()->getSession();

        // Ajoute le message flash
        $session->getFlashBag()->add(
            'success',
            'Thank you for visiting ! We hope to see you again soon.'
        );
    }
}