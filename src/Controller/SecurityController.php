<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Security\OAuthFormHandler;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use App\Security\AppAuthenticator;


class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);


    }

    #[Route(path: '/connect/registration/', name: 'hwi_oauth_connect_registration')]
    public function registerWithGoogle(
        Request $request,
        OAuthFormHandler $formHandler,
        UserAuthenticatorInterface $authenticator,
        AppAuthenticator $appAuthenticator
    ): Response {
        $form = $this->createForm(RegistrationFormType::class);
        $form->handleRequest($request);

        /** @var UserResponseInterface $userInformation */
        $userInformation = $request->attributes->get('hwi_oauth.user_information');

        if ($formHandler->process($request, $form, $userInformation)) {
            $user = $form->getData();

            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();

            return $authenticator->authenticateUser(
                $user,
                $appAuthenticator,
                $request
            );
        }

        return $this->render('registration/register_with_google.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
