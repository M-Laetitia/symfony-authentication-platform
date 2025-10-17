<?php
namespace App\Controller;

use App\Entity\Media;
use App\Enum\MediaType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class UploadController extends AbstractController
{
    #[Route('/uploadFile', name: 'upload_file', methods: ['POST'])]
    public function uploadFile(Request $request, EntityManagerInterface $em): JsonResponse
    {
        try {
            $file = $request->files->get('image');
            if (!$file) {
                return new JsonResponse(['success' => 0, 'message' => 'Aucun fichier uploadé.']);
            }

            $caption = $request->request->get('caption', '');
            // 1. Upload du fichier
            $filename = uniqid() . '.' . $file->guessExtension();
            $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads';
            $file->move($uploadsDir, $filename);
            $relativePath = '/uploads/' . $filename;

            // 2. Récupère les dimensions
            $imageInfo = @getimagesize($uploadsDir . '/' . $filename);
            $width = $imageInfo[0] ?? 1200;
            $height = $imageInfo[1] ?? 800;

            // 3. Crée et enregistre le média
            $media = new Media();
            $media->setPath($relativePath);
            $media->setAltText('');
            $media->setCaption($caption);
            // $media->setType('article_image');
            $media->setType(MediaType::ARTICLE_IMAGE);

            $em->persist($media);
            $em->flush();

            // 4. Renvoie la réponse
            return new JsonResponse([
                'success' => 1,
                'file' => [
                    'url' => $media->getPath(),
                    'id' => $media->getId(),
                    'width' => $width,
                    'height' => $height,
                ]
            ]);

        } catch (\Exception $e) {
            // Log l'erreur pour le débogage
            error_log($e->getMessage());
            return new JsonResponse([
                'success' => 0,
                'message' => 'Erreur lors de l\'upload : ' . $e->getMessage()
            ]);
        }
    }
}