<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserControllerTest extends WebTestCase
{       

    private $client;
    private $entityManager;
    private $passwordHasher;

    protected function setUp(): void
    {
        // Création du client
        $this->client = static::createClient();

        // Récupération de l'EntityManager
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();

        // Récupération du password hasher
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
    }

    public function testProfilePageRequiresLogin()
    {
        // création d'un client HTTP de test qui simule le navigateur
        $this->client->request('GET', '/profile');

        // On effectue une requête GET sur /profile sans être connecté
        $this->assertResponseRedirects('/login');

        // assertion : la réponse doit être une redirection vers /login
        // permet de vérifier que la page est protégée pour les utilisaters non authentifiés
        // $this->assertResponseRedirects('/login');
        $crawler = $this->client->followRedirect();
        $this->assertSelectorTextContains('h1', 'PROFILE');
    }

    public function testProfilePageAsUser()
    {
        // création d'unclient HTTP de test
        // $client = static::createClient();

        // Récupérer l'EntityManager depuis le container
        // permet de récupérer des services dans les tests fonctionnels.
        // $entityManager = static::getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class); 
        // $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        // Récupérer un  utilisateur de test depuis la base
        // $user = $entityManager->getRepository(User::class)
            // ->findOneBy(['email' => 'testuser@example.com']);

        $user = new User();
        $user->setEmail('testuser2@example.com');
        $user->setUsername('testuser2');
        // $user->setPassword($hashedPassword); 
        // utiliser UserPasswordHasherInterface si besoin
        $user->setPassword($this->passwordHasher->hashPassword($user, 'testpassword'));
        $this->entityManager->persist($user);
        $this->entityManager->flush();

            
        // Connexion simulée de l'utilisateur pour la session de  test
        $this->client->loginUser($user);

        // rRequête GET sur /profile en tant qu'utilisateur connecté
        $this->client->request('GET', '/profile');

        // vérifie que la page renvoie une réponse HTTP 200 (succès)
        $this->assertResponseIsSuccessful();

        // vérifie qu'un élément spécifique est présent
        $this->assertSelectorTextContains('h1', 'My profile');

        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    // public function testProfilePageAccessByRole()
    // {
    //     $user = new User();
    //     $user->setEmail('testuserrole@example.com');
    //     $user->setPassword($this->passwordHasher->hashPassword($user, 'testpassword'));
    //     $user->setRoles([]);
    //     $this->entityManager->persist($user);
    //     $this->entityManager->flush();
    //     $this->client->loginUser($user);
    //     $this->client->request('GET', '/profile');

    //     // Vérifie que l'accès est interdit
    //     $this->assertResponseStatusCodeSame(403);
    //     $this->entityManager->remove($user);
    //     $this->entityManager->flush();
    // }
}