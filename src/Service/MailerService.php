<?php
namespace App\Service;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class MailerService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendWelcomeEmail(User $user): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@domain.com', 'Website name'))
            ->to($user->getEmail())
            ->subject('Welcome !')
            ->htmlTemplate('emails/user/welcome.html.twig')
            ->context([
                'user' => $user,
            ]);

        $this->mailer->send($email);
    }

    public function sendAdminNotification(User $user): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@domain-name.com', 'Website name'))
            ->to('admin@domain-name.com')
            ->subject('New subscriber !')
            ->htmlTemplate('emails/admin/admin_notification.html.twig')
            ->context([
                'user' => $user,
            ]);

        $this->mailer->send($email);
    }
}
