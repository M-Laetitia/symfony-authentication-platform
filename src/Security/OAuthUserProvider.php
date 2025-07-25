<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class OAuthUserProvider implements OAuthAwareUserProviderInterface, UserProviderInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response): UserInterface
    {
        $googleId = $response->getUsername();
        $email = $response->getEmail();
        $pseudo = $response->getNickname();

        // Check if the user already exists with this Google ID
        $user = $this->entityManager->getRepository(User::class)
            ->findOneBy(['googleId' => $googleId]);

        if (!$user) {
            $user = new User();
            $user->setEmail($email);
            $user->setGoogleId($googleId);
            $user->setUsername($pseudo);
            $user->setPassword(uniqid('authGoogle_', true));
            $user->setRoles(['ROLE_USER']);

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        return $user;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => $identifier]);

        if (!$user) {
            throw new UserNotFoundException();
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class;
    }
}