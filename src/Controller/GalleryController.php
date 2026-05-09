<?php

namespace App\Controller;

use App\Repository\MediaRepository;
use App\Repository\SpecialityRepository;
use App\Service\SeoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GalleryController extends AbstractController
{
    #[Route('/gallery', name: 'gallery_index')]
    public function index(
        MediaRepository $mediaRepo,
        SpecialityRepository $specialityRepo,
        SeoService $seoService
    ): Response {
        $photos      = $mediaRepo->findGalleryPhotos(null);
        $specialities = $specialityRepo->findWithPhotos();

        return $this->render('gallery/index.html.twig', [
            'photos'           => $photos,
            'specialities'     => $specialities,
            'meta_title'       => $seoService->getMetaTitle('gallery'),
            'meta_description' => $seoService->getMetaDescription('gallery'),
            'meta_robots'      => $seoService->getMetaRobots('gallery'),
        ]);
    }
}
