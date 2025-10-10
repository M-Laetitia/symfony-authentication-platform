<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class UploadController extends AbstractController
{
    #[Route('/uploadFile', name: 'upload_file', methods: ['POST'])]
    public function uploadFile(Request $request): JsonResponse
    {
        $file = $request->files->get('image');
        if (!$file) {
            return new JsonResponse(['success' => 0, 'message' => 'Aucun fichier uploadé.']);
        }

        // Sauvegarde le fichier dans un dossier 
        $filename = uniqid() . '.' . $file->guessExtension();
        $file->move($this->getParameter('kernel.project_dir') . '/public/uploads', $filename);

        // Renvoie une réponse JSON valide pour Editor.js
        return new JsonResponse([
            'success' => 1,
            'file' => [
                'url' => '/uploads/' . $filename,
                'width' => 1200, 
                'height' => 800,
            ]
        ]);
    }
}
