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
    public function index(PhotographerRepository $photographerRepo, SeoService $seoService): Response
    {

        $photographers = $photographerRepo->findPhotographersWithCover();

        return $this->render('photographer/index.html.twig', [
            'photographers' => $photographers,
            'meta_title' => $seoService->getMetaTitle('team'),
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

    #[Route('/photographer/{slug}/portfolio', name: 'photographer_portfolio')]
    public function portfolio(PhotographerRepository $photographerRepository, SeoService $seoService, string $slug): Response
    {
        $photographer = $photographerRepository->findOneBy(['slug' => $slug]);

        if (!$photographer) {
            throw $this->createNotFoundException('Photographer not found');
        }

        $gallerySeries = $photographer->getGallerySeries();

        return $this->render('photographer/portfolio.html.twig', [
            'photographer' => $photographer,
            'gallerySeries' => $gallerySeries,
            'meta_title' => $photographer->getFirstName() . ' ' . $photographer->getLastName() . ' - Portfolio | MOSAIC',
            'meta_description' => $seoService->getMetaDescription('portfolio'),
            'meta_robots' => $seoService->getMetaRobots('portfolio') ?? 'index, follow',
        ]);
    }
}