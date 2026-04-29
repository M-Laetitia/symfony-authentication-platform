<?php

namespace App\Service;

use App\Entity\Media;
use App\Enum\MediaType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;

class EditorJsMediaUploader
{
    public function __construct(
        private MediaUploaderInterface $mediaUploader // Injection de dépendance : une classe qui implémente MediaUploaderInterface
    ) {}

    /**
     * Upload pour Editor.js et retourne une réponse JSON adaptée.
     */
    public function uploadForEditorJs(UploadedFile $file, string $caption = '', string $altText = ''): JsonResponse
    {
        try {
            $media = $this->mediaUploader->upload(
                $file, 
                $caption, 
                $altText, 
                MediaType::ARTICLE_IMAGE,
                'articles_content'  // Sous-dossier pour les images EditorJS
            );

            return new JsonResponse([
                'success' => 1,
                'file' => [
                    'url' => $media->getPath(),
                    'id' => $media->getId(),
                    'width' => $media->getWidth(),
                    'height' => $media->getHeight(),
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => 0,
                'message' => 'Erreur : ' . $e->getMessage()
            ]);
        }
    }
}