<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function profile(): Response
    {
        // Récupère l'utilisateur connecté via AbstractController
        $user = $this->getUser();

        // Si aucun utilisateur => redirection vers login
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Rend la vue avec l'utilisateur
        return $this->render('user/profile.html.twig', [
            'user' => $user,
        ]);
    }
}