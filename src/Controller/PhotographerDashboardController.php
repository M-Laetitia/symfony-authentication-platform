<?php

namespace App\Controller;

use App\Entity\Photographer;
use App\Enum\MediaType;
use App\Enum\PhotographerStatusType;
use App\Enum\PhotographerVisibilityType;
use App\Form\PhotographerProfileFormType;
use App\Repository\PhotographerRepository;
use App\Service\MediaUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_PHOTOGRAPHER')]
#[Route('/photographer/dashboard/{slug}', name: 'photographer_dashboard_')]
class PhotographerDashboardController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(string $slug, PhotographerRepository $photographerRepo): Response
    {
        $photographer = $this->getPhotographerBySlugOrThrow($slug, $photographerRepo);

        return $this->render('photographer/dashboard/index.html.twig', [
            'photographer' => $photographer,
        ]);
    }

    #[Route('/profile', name: 'profile_edit')]
    public function editProfile(
        string $slug,
        Request $request,
        PhotographerRepository $photographerRepo,
        EntityManagerInterface $em,
        MediaUploader $mediaUploader
    ): Response {
        $photographer = $this->getPhotographerBySlugOrThrow($slug, $photographerRepo);

        // Pre-fill form with existing data
        $formData = $this->prepareFormData($photographer);
        
        $form = $this->createForm(PhotographerProfileFormType::class, $photographer);
        
        // Manually set data from profile JSON
        $form->get('bioQuote')->setData($formData['bioQuote']);
        $form->get('bioShort')->setData($formData['bioShort']);
        $form->get('bioLong')->setData($formData['bioLong']);
        $form->get('location')->setData($formData['location']);
        $form->get('languages')->setData($formData['languages']);
        $form->get('experienceYears')->setData($formData['experienceYears']);
        $form->get('shootingsCount')->setData($formData['shootingsCount']);
        $form->get('equipment')->setData($formData['equipment']);
        $form->get('website')->setData($formData['website']);
        $form->get('instagram')->setData($formData['instagram']);
        $form->get('behance')->setData($formData['behance']);
        $form->get('tiktok')->setData($formData['tiktok']);
        $form->get('youtube')->setData($formData['youtube']);
        $form->get('twitter')->setData($formData['twitter']);
        $form->get('facebook')->setData($formData['facebook']);
        $form->get('status')->setData($formData['status']);
        $form->get('visibility')->setData($formData['visibility']);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Transform form data and save to photographer
            $this->hydratePhotographerFromForm($photographer, $form, $mediaUploader, $em);
            $em->persist($photographer);
            $em->flush();

            $this->addFlash('success', 'Your profile has been updated successfully!');

            return $this->redirectToRoute('photographer_dashboard_profile_edit', ['slug' => $slug]);
        }

        return $this->render('photographer/dashboard/profile-edit.html.twig', [
            'form' => $form,
            'photographer' => $photographer,
        ]);
    }

    /**
     * Retrieve photographer by slug and verify ownership
     * Throws 404 if not found, 403 if not owner
     */
    private function getPhotographerBySlugOrThrow(
        string $slug,
        PhotographerRepository $photographerRepo
    ): Photographer {
        $photographer = $photographerRepo->findOneBy(['slug' => $slug]);

        if (!$photographer) {
            throw $this->createNotFoundException('Photographer not found');
        }

        $currentUser = $this->getUser();

        if ($currentUser !== $photographer->getUser()) {
            throw $this->createAccessDeniedException('You can only access your own dashboard');
        }

        return $photographer;
    }

    /**
     * Prepare form data from photographer entity
     */
    private function prepareFormData(Photographer $photographer): array
    {
        $profile = $photographer->getProfile() ?? [];
        
        $bio = $profile['bio'] ?? [];
        $info = $profile['info'] ?? [];
        $stats = $profile['stats'] ?? [];
        $links = $profile['links'] ?? [];
        $socials = $links['socials'] ?? [];

        return [
            'bioQuote' => $bio['quote'] ?? null,
            'bioShort' => $bio['short'] ?? null,
            'bioLong' => $bio['long'] ?? null,
            'location' => $info['location'] ?? null,
            'languages' => is_array($info['languages'] ?? null) ? implode(', ', $info['languages']) : null,
            'experienceYears' => $stats['experience_years'] ?? null,
            'shootingsCount' => $stats['shootings_count'] ?? null,
            'equipment' => is_array($profile['equipment'] ?? null) ? implode(', ', $profile['equipment']) : null,
            'website' => $links['website'] ?? null,
            'instagram' => $socials['instagram'] ?? null,
            'behance' => $socials['behance'] ?? null,
            'tiktok' => $socials['tiktok'] ?? null,
            'youtube' => $socials['youtube'] ?? null,
            'twitter' => $socials['twitter'] ?? null,
            'facebook' => $socials['facebook'] ?? null,
            'status' => !empty($photographer->getStatus()) ? $photographer->getStatus()[0]->value : PhotographerStatusType::INACTIVE->value,
            'visibility' => !empty($photographer->getVisibility()) ? $photographer->getVisibility()[0]->value : PhotographerVisibilityType::PRIVATE->value,
        ];
    }

    /**
     * Hydrate photographer entity from form data
     */
    private function hydratePhotographerFromForm(
        Photographer $photographer,
        $form,
        MediaUploader $mediaUploader,
        EntityManagerInterface $em
    ): void {
        if (!$photographer->getProfile()) {
            $photographer->setProfile([]);
        }

        $profile = $photographer->getProfile();

        // Bio section
        $profile['bio']['quote'] = $form->get('bioQuote')->getData();
        $profile['bio']['short'] = $form->get('bioShort')->getData();
        $profile['bio']['long'] = $form->get('bioLong')->getData();

        // Info section
        $profile['info']['location'] = $form->get('location')->getData();
        $languagesStr = $form->get('languages')->getData();
        $profile['info']['languages'] = $languagesStr ? array_map('trim', explode(',', $languagesStr)) : [];

        // Stats section
        $profile['stats']['experience_years'] = $form->get('experienceYears')->getData();
        $profile['stats']['shootings_count'] = $form->get('shootingsCount')->getData();

        // Equipment
        $equipmentStr = $form->get('equipment')->getData();
        $profile['equipment'] = $equipmentStr ? array_map('trim', explode(',', $equipmentStr)) : [];

        // Links section
        $profile['links']['website'] = $form->get('website')->getData();
        $profile['links']['socials'] = [
            'instagram' => $form->get('instagram')->getData(),
            'behance' => $form->get('behance')->getData(),
            'tiktok' => $form->get('tiktok')->getData(),
            'youtube' => $form->get('youtube')->getData(),
            'twitter' => $form->get('twitter')->getData(),
            'facebook' => $form->get('facebook')->getData(),
        ];

        $photographer->setProfile($profile);

        // Status and Visibility (from enum)
        $statusValue = $form->get('status')->getData();
        $visibilityValue = $form->get('visibility')->getData();
        $photographer->setStatus(PhotographerStatusType::from($statusValue));
        $photographer->setVisibility(PhotographerVisibilityType::from($visibilityValue));

        // Portfolio Cover Image
        $portfolioImageFile = $form->get('portfolioCoverImage')->getData();
        if ($portfolioImageFile) {
            $altText = $form->get('portfolioCoverAltText')->getData() ?? '';
            
            // Delete old portfolio cover if exists
            foreach ($photographer->getMedia()->toArray() as $media) {
                if ($media->getType() === MediaType::PORTFOLIO_COVER) {
                    $mediaUploader->deleteMediaFile($media);
                    $photographer->removeMedium($media);
                    $em->remove($media);
                }
            }

            // Upload new portfolio cover image
            $subfolder = "photographer/{$photographer->getId()}";
            $constraints = [
                'max_width' => 1600,
                'allowed_types' => ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'],
            ];

            $media = $mediaUploader->upload(
                $portfolioImageFile,
                'Portfolio Banner',
                $altText,
                MediaType::PORTFOLIO_COVER,
                $subfolder,
                $constraints
            );

            $photographer->addMedium($media);
        }
    }
}