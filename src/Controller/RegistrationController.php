<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationForm;
use App\Service\UserEmailVerificationService;
use App\Service\UserRegistrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


use App\Event\UserRegisteredEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private UserRegistrationService $userRegistrationService,
        private UserEmailVerificationService $emailVerificationService,
    ) {}

    #[Route('/register', name: 'app_register')]
    public function register(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // Déléguer toute la logique d'inscription au service
            $this->userRegistrationService->register($user, $plainPassword);

            $this->addFlash('success', [
                'text' => 'Your account has been created successfully.
                           We’ve sent you a confirmation email to verify your email address.
                           Please click the link in the email to activate your account before logging in.',
                'type' => 'registration'
            ]);

            // Le controller ne s'occupe que de la redirection
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request): Response
    {
        // Déléguer la recherche de l'utilisateur au service
        $user = $this->emailVerificationService->findUserFromRequest($request);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        // Déléguer la vérification au service
        $result = $this->emailVerificationService->verifyEmail($request, $user);

        // Le controller ne s'occupe que de la gestion des messages et redirections
        $this->addFlash($result['flash_type'], $result['message']);

        return $this->redirectToRoute('app_register');
    }
}
