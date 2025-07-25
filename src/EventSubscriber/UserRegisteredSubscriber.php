<?php
namespace App\EventSubscriber;

use App\Event\UserRegisteredEvent;
use App\Service\MailerService; 
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserRegisteredSubscriber implements EventSubscriberInterface
{
    private MailerService $mailer;

    public function __construct(MailerService $mailer)
    {
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserRegisteredEvent::NAME => 'onUserRegistered',
        ];
    }

    public function onUserRegistered(UserRegisteredEvent $event)
    {
        $user = $event->getUser();
        $this->mailer->sendWelcomeEmail($user);
        $this->mailer->sendAdminNotification($user);
    }
}
