<?php

namespace App\Security;

use App\Entity\User;
use UserRegistrationService;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Form\RegistrationFormHandlerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class OAuthFormHandler implements RegistrationFormHandlerInterface
{

    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher
    ) {
    }

    public function process(Request $request, FormInterface $form, UserResponseInterface $userInformation): bool
    {
        $user = new User();
        $userRegistrationService = new UserRegistrationService;
        $userRegistrationService->createUserFromForm($form, $user, $userInformation, $request);

        if ($form->isSubmitted() && $form->isValid()) {
            return true;
        }
        return false;
    }
}