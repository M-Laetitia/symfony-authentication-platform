<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Security\OAuthRegistrationFormHandler;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use App\Security\AppAuthenticator;
use App\Service\SeoService;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\MediaUploader;
use App\Entity\User;


class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, SeoService $seoService): Response
    {

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'meta_title' => $seoService->getMetaTitle('login'),
            'meta_description' => $seoService->getMetaDescription('login'),
            'meta_robots' => $seoService->getMetaRobots('login'),
        ]);

    }

    #[Route(path: '/connect/registration/', name: 'hwi_oauth_connect_registration')]
    public function registerWithGoogle(
        Request $request,
        OAuthRegistrationFormHandler $formHandler,
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

    #[Route('/account/delete', name: 'account_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function deleteAccount(EntityManagerInterface $em, MediaUploader $mediaUploader): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        $pseudoId = 'DEL_' . bin2hex(random_bytes(8));

        // Pseudonymisation of the user
        $user->setEmail($pseudoId . '@deleted.local');
        $user->setFirstName(null);
        $user->setLastName(null);
        $user->setGoogleId(null);
        $user->setUsername('user_' . $pseudoId);
        $user->setPassword(bin2hex(random_bytes(32)));
        $user->setRoles(['ROLE_DELETED']);

        // Delete the avatar if it exists
        if ($user->getAvatar()) {
            $mediaUploader->deleteMediaFile($user->getAvatar());
            $user->setAvatar(null);
        }

        $em->flush();

        // Deconnect the user after account deletion
        $this->container->get('security.token_storage')->setToken(null);

        $this->addFlash('success', 'Your account has been deleted.');
        
        return $this->redirectToRoute('home');
    }
}
