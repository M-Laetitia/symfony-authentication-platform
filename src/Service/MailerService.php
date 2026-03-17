<?php
namespace App\Service;

use App\Entity\User;
use App\Entity\Message;
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

    public function sendMessageReportedEmail(
        Message $message,
        User $reporter,
        string $reason
    ): void
    {
        $reportedUser = $message->getSender();
    
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@domain.com', 'Mosaic'))
            ->to($reportedUser->getEmail())
            ->subject('Your message has been reported')
            ->htmlTemplate('emails/user/message_reported.html.twig')
            ->context([
                'reportedUser' => $reportedUser,
                'reporter' => $reporter,
                'reason' => $reason,
                'messageContent' => $message->getContent(),
            ]);
    
        $this->mailer->send($email);
    }

    public function sendAdminMessageReportNotification(
        Message $message,
        User $reporter,
        string $reason
    ): void
    {
        $reportedUser = $message->getSender();
    
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@domain.com', 'Mosaic'))
            ->to('admin@domain.com')
            ->subject('A message has been reported')
            ->htmlTemplate('emails/admin/message_report_notification.html.twig')
            ->context([
                'reportedUser' => $reportedUser,
                'reporter' => $reporter,
                'reason' => $reason,
                'messageContent' => $message->getContent(),
                'conversationId' => $message->getConversation()->getId(),
                'messageId' => $message->getId(),
            ]);
    
        $this->mailer->send($email);
    }

    public function sendContactEmail(string $name, string $emailFrom, string $message, string $subject): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($emailFrom, $name))
            ->to('admin@mosaic.com')
            ->subject('New contact form message')
            ->htmlTemplate('emails/admin/contact.html.twig')
            ->context([
                'name' => $name,
                'subject' => $subject,
                'emailFrom' => $emailFrom,
                'messageContent' => $message,
            ]);

        $this->mailer->send($email);
    }
}
