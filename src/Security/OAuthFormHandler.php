<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Form\RegistrationFormHandlerInterface;
use App\Entity\User;

final class OAuthFormHandler implements RegistrationFormHandlerInterface
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher
    ) {
    }

    public function process(Request $request, FormInterface $form, UserResponseInterface $userInformation): bool
    {
        $user = new User();
        $user->setEmail($userInformation->getEmail());
        $user->setUsername($userInformation->getNickname());
        $user->setFirstName($userInformation->getFirstName());
        $user->setLastName($userInformation->getLastName());

        $form->setData($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            return true;
        }

        return false;
    }
}
