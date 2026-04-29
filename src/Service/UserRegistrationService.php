<?php
namespace App\Service;

use App\Entity\User;
use App\Event\UserRegisteredEvent;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class UserRegistrationService
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager,
        private EmailVerifier $emailVerifier,
        private EventDispatcherInterface $dispatcher,
        private LoggerInterface $logger,
    ) {}

    public function register(User $user, string $plainPassword): void
    {
        // 1. Hacher le mot de passe
        // $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        //  dump('register called for ' . $user->getEmail());
        try {
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, $plainPassword)
            );
        } catch (\Throwable $e) {
            $this->logger->error('Error while hashing the user password.', [
                'exception' => $e,
                'email' => $user->getEmail(),
            ]);
        
            throw new \RuntimeException('Unable to create the user', 0, $e);
        }
        
        // 2. Définir le rôle par défaut
        $user->setRoles(['ROLE_USER']);
        
        // 3. Persister l'utilisateur
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // 4. Envoyer l'email de confirmation
        try {
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('mailer@your-domain.com', 'Acme Mail bot'))
                    ->to((string) $user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

        } catch (\Throwable $e){
            $this->logger->error('Error sending email:  '.$e->getMessage());
            throw new \RuntimeException('Unable to send confirmation email');
        };
        // $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
        //     (new TemplatedEmail())
        //         ->from(new Address('mailer@your-domain.com', 'Acme Mail bot'))
        //         ->to((string) $user->getEmail())
        //         ->subject('Please Confirm your Email')
        //         ->htmlTemplate('registration/confirmation_email.html.twig')
        // );

        // 5. Dispatcher l'événement
        $event = new UserRegisteredEvent($user);
        $this->dispatcher->dispatch($event, UserRegisteredEvent::NAME);
    }
}