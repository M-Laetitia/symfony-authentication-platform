<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;

final class OAuthUserProvider implements OAuthAwareUserProviderInterface
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response): \Symfony\Component\Security\Core\User\UserInterface
    {
        $email = $response->getEmail();
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            throw new AccountNotLinkedException(sprintf('No user with email "%s" was found.', $email));
        }

        return $user;
    }
}