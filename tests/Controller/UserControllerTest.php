<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;

class UserControllerTest extends WebTestCase
{
    public function testProfilePageRequiresLogin()
    {
        // création d'un client HTTP de test qui simule le navigateur
        $client = static::createClient();

        // On effectue une requête GET sur /profile sans être connecté
        $client->request('GET', '/profile');

        // assertion : la réponse doit être une redirection vers /login
        // permet de vérifier que la page est protégée pour les utilisaters non authentifiés
        $this->assertResponseRedirects('/login');
    }

    public function testProfilePageAsUser()
    {
        // création d'unclient HTTP de test
        $client = static::createClient();

        // Récupérer l'EntityManager depuis le container
        $entityManager = static::getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class); // permet de récupérer des services dans les tests fonctionnels.

        // Récupérer un  utilisateur de test depuis la base
        $user = $entityManager->getRepository(User::class)
            ->findOneByEmail('testuser@exemple.com');

        // Connexion simulée de l'utilisateur pour la session de  test
        $client->loginUser($user);

        // rRequête GET sur /profile en tant qu'utilisateur connecté
        $crawler = $client->request('GET', '/profile');

        // vérifie que la page renvoie une réponse HTTP 200 (succès)
        $this->assertResponseIsSuccessful();

        // vérifie qu'un élément spécifique est présent
        $this->assertSelectorTextContains('h1', 'Mon profil');
    }
}