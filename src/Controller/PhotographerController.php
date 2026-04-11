<?php

namespace App\Controller;

use App\Repository\MediaRepository;
use App\Repository\PhotographerRepository;
use App\Service\SeoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PhotographerController extends AbstractController
{
    #[Route('/team', name: 'team_index')]
    public function index(PhotographerRepository $photographerRepository, SeoService $seoService): Response
    {

        $photographers = $photographerRepository->findAll();

        return $this->render('photographer/index.html.twig', [
            'photographers' => $photographers,
            'meta_description' => $seoService->getMetaDescription('team'),
            'meta_robots' => $seoService->getMetaRobots('team'),

        ]);
    }

    #[Route('/team/{slug}', name: 'team_show')]
    public function show(PhotographerRepository $photographerRepository, MediaRepository $mediaRepository,  string $slug): Response
    {
        $photographer = $photographerRepository->findOneBy(['slug' => $slug]);

        if (!$photographer) {
            throw $this->createNotFoundException('Photographer not found');
        }

        $bannerImage = $mediaRepository->findPortfolioCoverByPhotographer($photographer);
        $featuredMedias = $mediaRepository->findFeaturedByPhotographer($photographer);
       

        return $this->render('photographer/show.html.twig', [
            'photographer' => $photographer,
            'bannerImage' => $bannerImage,
            'featuredMedias' => $featuredMedias,
        ]);
        
    }
}