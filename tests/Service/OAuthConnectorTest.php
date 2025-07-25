<?php

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\OAuthConnector;
use Doctrine\ORM\EntityManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use PHPUnit\Framework\TestCase;

class OAuthConnectorTest extends TestCase
{
    // Permet de vérifier que lorsqu'un user se connecte via Google OAuth le service OAuthConnector récupère le nom du fournisseur (google), l'id fourni, met à jour la propriété correspondante de l'user (googleId), persiste l'use en bdd via Doctrine (persist / flush)
    public function testConnectUpdatesUserWithOAuthId(): void
    {
        // 1. on crée un faux fournisseur  OAuth (Google ici)
        $resourceOwner = $this->createMock(ResourceOwnerInterface::class);
        $resourceOwner->method('getName')->willReturn('google');

        // 2. On crée une fausse réponse OAuth simulant ce que Google renverrait
        $response = $this->createMock(UserResponseInterface::class);
        $response->method('getResourceOwner')->willReturn($resourceOwner);
        $response->method('getUsername')->willReturn('123456'); // identifiant Google

        // 3. On instancie un vrai utilisateur Symfony (pour que PropertyAccessor fonctionne)
        $user = new User(); // classe concrète avec setGoogleId()

        // 4. On crée un faux EntityManager et on s’assure qu’il persiste et flush bien l’utilisateur
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('persist')->with($user);
        $entityManager->expects($this->once())->method('flush');

        // 5. On instancie le service à tester (avec la config qui dit d’écrire dans la propriété "googleId")
        $connector = new OAuthConnector($entityManager, ['google' => 'googleId']);
        // 6. On appelle la méthode `connect()` comme si un login via Google avait eu lieu
        $connector->connect($user, $response);

        // 7. On vérifie que l'utilisateur a bien reçu l'ID Google dans la propriété googleId
        $this->assertSame('123456', $user->getGoogleId());
    }

    // A FAIRE : le cas où le provider (google) n'est pas dans le tableau de $properties cela doit faire un return sans rien faire , test à faire : on vérifie que persist() et flush() ne sont pas appelés

    

    // public function testConnectDoesNothingIfNoMapping(): void
    // {
    //     $user = $this->createMock(UserInterface::class);
    //     $response = $this->createMock(UserResponseInterface::class);
    //     $resourceOwner = $this->createMock(\HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface::class);

    //     $response->method('getResourceOwner')->willReturn($resourceOwner);
    //     $resourceOwner->method('getName')->willReturn('github'); // Pas dans le mapping

    //     $entityManager = $this->createMock(EntityManagerInterface::class);
    //     $entityManager->expects($this->never())->method('persist');
    //     $entityManager->expects($this->never())->method('flush');

    //     $connector = new OAuthConnector($entityManager, ['google' => 'googleId']);
    //     $connector->connect($user, $response);
    // }
}