<?php
namespace App\Tests\Service;

use App\Entity\User;
use App\Service\UserRegistrationService;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UserRegistrationServiceTest extends TestCase
{
    public function testRegisterUser()
    {
        // Step 0 : on crée les mocks pour les dépendances 
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $emailVerifier = $this->createMock(EmailVerifier::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        // Step 1 : création d'un utilisateur "fictif" avec un email valide (obligatoire pour envoyer l'email)
        $user = new User();
        $user->setEmail('test@example.com');
        // dump($user);

        // Step 2 : création d’un faux password hasher (mocké)
        // on vérifie qu’il sera bien appelé avec les bons arguments, et on force le résultat retourné
        $passwordHasher->expects($this->once()) // doit être appelé exactement 1 fois
            ->method('hashPassword') // on attend l’appel à cette méthode
            ->with($user, 'plain-password') // avec ces arguments
            ->willReturn('hashed-password');  // elle retournera cette valeur

        //Step 3 :  On vérifie qu’il persiste et flush bien l'utilisateur
        $entityManager->expects($this->once())->method('persist')->with($user);
        $entityManager->expects($this->once())->method('flush');

        // On vérifie que EmailVerifier appelle bien la méthode d’envoi de l’email
        $emailVerifier->expects($this->once())
        ->method('sendEmailConfirmation')
            ->with(
                $this->equalTo('app_verify_email'),
                $this->identicalTo($user),
                $this->isInstanceOf(TemplatedEmail::class)
            );

        // On vérifie que EventDispatcher déclenche bien l’événement d’inscription    
        $dispatcher->expects($this->once())->method('dispatch');

        // Step 6 : Création du service à tester, avec tous les services mockés injectés
        $registrationService = new UserRegistrationService(
            $passwordHasher,
            $entityManager,
            $emailVerifier,
            $dispatcher,
            $logger
        );


        // Step 7 : Exécution de la méthode à tester
        $registrationService->register($user, 'plain-password');

        // Step 8 : Assertions finales
        // On vérifie que le rôle par défaut est bien défini
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
    
        // et que le mdp est une string et a bien été défini avec la valeur attendue
        $this->assertIsString($user->getPassword(), 'The hashed-password must be a string');
        $this->assertEquals('hashed-password', $user->getPassword());
    }

    public function testRegisterUserThrowsExceptionIfHashFails(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to create the user');

        $user = new User();
        $user->setEmail('fail@example.com');

        // Hasher simulé qui jette une exception
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->willThrowException(new \RuntimeException('Hashing failed'));

        $registrationService = new UserRegistrationService(
            $passwordHasher,
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(EmailVerifier::class),
            $this->createMock(EventDispatcherInterface::class),
            $this->createMock(LoggerInterface::class),
        );

        $registrationService->register($user, 'some-password');
    }

    public function testRegisterUserThrowsExceptionIfEmailFails(): void
    {
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $emailVerifier = $this->createMock(EmailVerifier::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $user = new User();
        $user->setEmail('test@example.com');

        $emailVerifier->expects($this->once())
            ->method('sendEmailConfirmation')
            ->willThrowException(new \Exception('SMTP failure'));

        // Ne pas attendre de dispatch dans ce test
        $dispatcher->expects($this->never())->method('dispatch');

        $registrationService = new UserRegistrationService(
            $passwordHasher,
            $entityManager,
            $emailVerifier,
            $dispatcher,
            $logger
        );


        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to send confirmation email');

        $registrationService->register($user, 'plain-password');
    }
}
