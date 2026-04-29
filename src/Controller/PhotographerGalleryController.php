<?php

namespace App\Controller;

use App\Entity\GallerySeries;
use App\Entity\Media;
use App\Entity\Photographer;
use App\Enum\GallerySeriesType;
use App\Enum\MediaType;
use App\Form\MediaGalleryAddFormType;
use App\Repository\GallerySeriesRepository;
use App\Repository\PhotographerRepository;
use App\Service\MediaUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_PHOTOGRAPHER')]
#[Route('/photographer/dashboard/{slug}/galleries', name: 'photographer_gallery_')]
class PhotographerGalleryController extends AbstractController
{
    /**
     * List all galleries for the photographer
     */
    #[Route('', name: 'index')]
    public function index(
        string $slug,
        PhotographerRepository $photographerRepo
    ): Response {
        $photographer = $this->getPhotographerBySlugOrThrow($slug, $photographerRepo);

        return $this->render('photographer/dashboard/galleries/index.html.twig', [
            'photographer' => $photographer,
            'galleries' => $photographer->getGallerySeries(),
        ]);
    }

    /**
     * Create a new gallery series
     */
    #[Route('/create', name: 'create')]
    public function create(
        string $slug,
        Request $request,
        PhotographerRepository $photographerRepo,
        EntityManagerInterface $em
    ): Response {
        $photographer = $this->getPhotographerBySlugOrThrow($slug, $photographerRepo);

        $gallery = new GallerySeries();
        $gallery->setPhotographer($photographer);

        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $description = $request->request->get('description');

            if ($name) {
                $gallery->setName($name);
                $gallery->setDescription($description);
                $gallery->setType(GallerySeriesType::GALLERY);
                $em->persist($gallery);
                $em->flush();

                $this->addFlash('success', 'Gallery created successfully!');
                return $this->redirectToRoute('photographer_gallery_index', ['slug' => $slug]);
            }
        }

        return $this->render('photographer/dashboard/galleries/create.html.twig', [
            'photographer' => $photographer,
            'gallery' => $gallery,
        ]);
    }

    /**
     * Edit a gallery
     */
    #[Route('/{galleryId}/edit', name: 'edit')]
    public function edit(
        string $slug,
        int $galleryId,
        Request $request,
        PhotographerRepository $photographerRepo,
        GallerySeriesRepository $galleryRepo,
        EntityManagerInterface $em
    ): Response {
        $photographer = $this->getPhotographerBySlugOrThrow($slug, $photographerRepo);
        $gallery = $this->getGalleryByIdOrThrow($galleryId, $photographer, $galleryRepo);

        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $description = $request->request->get('description');

            if ($name) {
                $gallery->setName($name);
                $gallery->setDescription($description);
                $em->flush();

                $this->addFlash('success', 'Gallery updated successfully!');
                return $this->redirectToRoute('photographer_gallery_index', ['slug' => $slug]);
            }
        }

        return $this->render('photographer/dashboard/galleries/edit.html.twig', [
            'photographer' => $photographer,
            'gallery' => $gallery,
        ]);
    }

    /**
     * Delete a gallery
     */
    #[Route('/{galleryId}/delete', name: 'delete')]
    public function delete(
        string $slug,
        int $galleryId,
        PhotographerRepository $photographerRepo,
        GallerySeriesRepository $galleryRepo,
        MediaUploader $mediaUploader,
        EntityManagerInterface $em
    ): Response {
        $photographer = $this->getPhotographerBySlugOrThrow($slug, $photographerRepo);
        $gallery = $this->getGalleryByIdOrThrow($galleryId, $photographer, $galleryRepo);

        // Delete all media files and entities
        foreach ($gallery->getMedias()->toArray() as $media) {
            $mediaUploader->deleteMediaFile($media);
            $em->remove($media);
        }

        // Delete the gallery folder itself
        $galleryPath = $this->getParameter('kernel.project_dir') . '/public/uploads/photographer/' . $photographer->getId() . '/galleries/' . $galleryId;
        if (is_dir($galleryPath)) {
            @rmdir($galleryPath);
        }

        $em->remove($gallery);
        $em->flush();

        $this->addFlash('success', 'Gallery deleted successfully!');
        return $this->redirectToRoute('photographer_gallery_index', ['slug' => $slug]);
    }

    /**
     * Add media to gallery
     */
    #[Route('/{galleryId}/media/add', name: 'media_add')]
    public function addMedia(
        string $slug,
        int $galleryId,
        Request $request,
        PhotographerRepository $photographerRepo,
        GallerySeriesRepository $galleryRepo,
        MediaUploader $mediaUploader,
        EntityManagerInterface $em
    ): Response {
        $photographer = $this->getPhotographerBySlugOrThrow($slug, $photographerRepo);
        $gallery = $this->getGalleryByIdOrThrow($galleryId, $photographer, $galleryRepo);

        $form = $this->createForm(MediaGalleryAddFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $uploadedFile = $request->files->get('media_gallery_add_form')['media'];
            $altText = $data['altText'] ?? '';
            $caption = $data['caption'] ?? '';
            $isFeatured = $data['featured'] ?? false;

            if ($uploadedFile) {
                $subfolder = "photographer/{$photographer->getId()}/galleries/{$galleryId}";
                $constraints = ['allowed_types' => ['image/jpeg', 'image/png', 'image/jpg', 'image/webp']];
                $mediaType = $isFeatured ? MediaType::PORTFOLIO_FEATURED : MediaType::GALLERY_SERIES;

                $media = $mediaUploader->upload(
                    $uploadedFile,
                    $caption,
                    $altText,
                    $mediaType,
                    $subfolder,
                    $constraints
                );

                $gallery->addMedia($media);
                $photographer->addMedium($media);
                $em->flush();

                $this->addFlash('success', 'Photo added to gallery!');
                return $this->redirectToRoute('photographer_gallery_edit', ['slug' => $slug, 'galleryId' => $galleryId]);
            }
        }

        return $this->render('photographer/dashboard/galleries/media-add.html.twig', [
            'photographer' => $photographer,
            'gallery' => $gallery,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Edit media in gallery
     */
    #[Route('/{galleryId}/media/{mediaId}/edit', name: 'media_edit')]
    public function editMedia(
        string $slug,
        int $galleryId,
        int $mediaId,
        Request $request,
        PhotographerRepository $photographerRepo,
        GallerySeriesRepository $galleryRepo,
        EntityManagerInterface $em
    ): Response {
        $photographer = $this->getPhotographerBySlugOrThrow($slug, $photographerRepo);
        $gallery = $this->getGalleryByIdOrThrow($galleryId, $photographer, $galleryRepo);
        $media = $this->getMediaByIdOrThrow($mediaId, $gallery);

        if ($request->isMethod('POST')) {
            $altText = $request->request->get('altText', '');
            $caption = $request->request->get('caption', '');
            $isFeatured = $request->request->has('isFeatured');

            $media->setAltText($altText);
            $media->setCaption($caption);

            // Toggle featured status
            $currentType = $media->getType();
            if ($isFeatured && $currentType !== MediaType::PORTFOLIO_FEATURED) {
                $media->setType(MediaType::PORTFOLIO_FEATURED);
            } elseif (!$isFeatured && $currentType === MediaType::PORTFOLIO_FEATURED) {
                $media->setType(MediaType::GALLERY_SERIES);
            }

            $em->flush();
            $this->addFlash('success', 'Photo updated successfully!');
            return $this->redirectToRoute('photographer_gallery_edit', ['slug' => $slug, 'galleryId' => $galleryId]);
        }

        return $this->render('photographer/dashboard/galleries/media-edit.html.twig', [
            'photographer' => $photographer,
            'gallery' => $gallery,
            'media' => $media,
        ]);
    }

    /**
     * Remove media from gallery
     */
    #[Route('/{galleryId}/media/{mediaId}/delete', name: 'media_delete')]
    public function deleteMedia(
        string $slug,
        int $galleryId,
        int $mediaId,
        PhotographerRepository $photographerRepo,
        GallerySeriesRepository $galleryRepo,
        MediaUploader $mediaUploader,
        EntityManagerInterface $em
    ): Response {
        $photographer = $this->getPhotographerBySlugOrThrow($slug, $photographerRepo);
        $gallery = $this->getGalleryByIdOrThrow($galleryId, $photographer, $galleryRepo);
        $media = $this->getMediaByIdOrThrow($mediaId, $gallery);

        $mediaUploader->deleteMediaFile($media);
        $gallery->removeMedia($media);
        $em->remove($media);
        $em->flush();

        $this->addFlash('success', 'Photo removed from gallery!');
        return $this->redirectToRoute('photographer_gallery_edit', ['slug' => $slug, 'galleryId' => $galleryId]);
    }

    /**
     * Get photographer by slug and verify ownership
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
            throw $this->createAccessDeniedException('You can only access your own galleries');
        }

        return $photographer;
    }

    /**
     * Get gallery by ID and verify ownership
     */
    private function getGalleryByIdOrThrow(
        int $galleryId,
        Photographer $photographer,
        GallerySeriesRepository $galleryRepo
    ): GallerySeries {
        $gallery = $galleryRepo->find($galleryId);

        if (!$gallery || $gallery->getPhotographer() !== $photographer) {
            throw $this->createAccessDeniedException('This gallery does not belong to you');
        }

        return $gallery;
    }

    /**
     * Get media by ID and verify it belongs to the gallery
     */
    private function getMediaByIdOrThrow(int $mediaId, GallerySeries $gallery): Media {
        foreach ($gallery->getMedias() as $media) {
            if ($media->getId() === $mediaId) {
                return $media;
            }
        }

        throw $this->createNotFoundException('Media not found in this gallery');
    }
}
