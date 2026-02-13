<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    #[IsGranted('ROLE_USER')]
    public function profile(): Response
    {
        // Récupère l'utilisateur connecté via AbstractController
        $user = $this->getUser();

        // Si aucun utilisateur => redirection vers login
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // if (!$this->isGranted('ROLE_ADMIN')) {
        //     throw $this->createAccessDeniedException('Accès refusé : rôle insuffisant.');
        // }

        // Rend la vue avec l'utilisateur
        return $this->render('user/profile.html.twig', [
            'user' => $user,
        ]);
    }
}