<?php
namespace App\Controller;

use App\Enum\MediaType;
use App\Service\MediaUploaderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class UploadController extends AbstractController
{
    public function __construct(
        private MediaUploaderInterface $mediaUploader
    ) {}

    #[Route('/uploadFile', name: 'upload_file', methods: ['POST'])]
    public function uploadFile(Request $request): JsonResponse
    {
        try {
            $file = $request->files->get('image');
            if (!$file) {
                return new JsonResponse(['success' => 0, 'message' => 'Aucun fichier uploadé.']);
            }

            // Récupère le caption et le altText
            $caption = $request->request->get('caption', '');
            $altText = $request->request->get('alt', '');

            // Contraintes pour EditorJS
            $constraints = [
                'max_size' => '2M',         
                'min_width' => 400,           
                'min_height' => 400,          
                'max_width' => 1200,         
                'max_height' => 800,        
            ];

            // Upload via MediaUploader dans articles_content avec Media entity
            $media = $this->mediaUploader->upload(
                $file, 
                $caption, 
                $altText, 
                MediaType::ARTICLE_IMAGE,
                'articles_content',  // Sous-dossier pour EditorJS
                $constraints
            );

            // 4. Renvoie la réponse au format EditorJS
            return new JsonResponse([
                'success' => 1,
                'file' => [
                    'url' => '/uploads/' . $media->getPath(),
                    'id' => $media->getId(),
                    'width' => $media->getWidth(),
                    'height' => $media->getHeight(),
                ]
            ]);

        } catch (\Exception $e) {
            error_log($e->getMessage());
            return new JsonResponse([
                'success' => 0,
                'message' => $e->getMessage()
            ]);
        }
    }
}