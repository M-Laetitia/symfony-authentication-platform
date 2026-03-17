<?php

namespace App\Controller;

use App\Repository\PhotographerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PhotographerController extends AbstractController
{
    #[Route('/team', name: 'team_index')]
    public function index(PhotographerRepository $photographerRepository): Response
    {

        $photographers = $photographerRepository->findAll();

        return $this->render('photographer/index.html.twig', [
            'photographers' => $photographers,
        ]);
    }
}