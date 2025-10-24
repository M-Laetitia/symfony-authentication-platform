<?php 

namespace App\Service;

use App\Entity\Media;
use App\Enum\MediaType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaUploader implements MediaUploaderInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private string $uploadsDir
    ) {}

    /**
     * Upload un fichier + crée un objet Media.
     *
     * @param UploadedFile $file
     * @param string $caption
     * @param string $altText
     * @param MediaType $type
     * @param string|null $subfolder
     * @param array $constraints 
     * @return Media
     */

    public function upload(
        UploadedFile $file,
        string $caption = '',
        string $altText = '',
        MediaType $type = MediaType::DEFAULT,
        ?string $subfolder = null,
        array $constraints = []
    ): Media {
        // Implémentation de la méthode définie dans l'interface
        
        // 1. Merge des contraintes par défaut et celles passées
        $defaultConstraints = [
            'max_size' => '3M',
            'allowed_types' => ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'],
        ];
        $constraints = array_merge($defaultConstraints, $constraints);
    
        // 2. Vérifie que le fichier respecte les contraintes
        $this->validateFile($file, $constraints);
        
        // 3. Détermine le dossier final d'upload
        $uploadDir = $this->uploadsDir;
        if ($subfolder) {
            $uploadDir .= '/' . trim($subfolder, '/');
        }
        // Crée le dossier si nécessaire
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); //0777 : permissions du dossier (lecture, écriture, exécution pour tout le monde)
        }

        // 4. Génère un nom de fichier unique + crée le chemin relatif pour la bdd
        $filename = uniqid() . '.' . $file->guessExtension();
        $relativePath = '/uploads' . ($subfolder ? '/' . trim($subfolder, '/') : '') . '/' . $filename;

        // 5. Déplace le fichier vers le dossier d'uploads
        $file->move($uploadDir, $filename);

        // 6. Récupère les dimensions de l'image
        $imageInfo = @getimagesize($this->uploadsDir . '/' . $filename);
        $width = $imageInfo[0] ?? 800;
        $height = $imageInfo[1] ?? 400;

        // $width = 800;
        // $height = 400;

        // 7. Crée et persiste le nouvel objet Media
        $media = new Media();
        $media->setPath($relativePath);
        $media->setCaption($caption);
        $media->setAltText($altText);
        $media->setType($type);
        $media->setWidth($width);
        $media->setHeight($height);

        $this->em->persist($media);
        $this->em->flush();

        return $media;
    }

    private function validateFile(UploadedFile $file, array $constraints): void
    {
        // Vérifie la taille max
        if (isset($constraints['max_size'])) {
            $maxSize = $constraints['max_size'];
    
            // Si $maxSize est une string, convertir en octets
            if (is_string($maxSize)) {
                $maxSize = trim($maxSize);
                $unit = strtoupper(substr($maxSize, -1));
                $size = (int) substr($maxSize, 0, -1);
    
                switch ($unit) {
                    case 'K': $maxSize = $size * 1024; break;         
                    case 'M': $maxSize = $size * 1024 * 1024; break;   
                    case 'G': $maxSize = $size * 1024 * 1024 * 1024; break;
                    default: $maxSize = (int) $maxSize; 
                }
            }
    
            if ($file->getSize() > $maxSize) {
                throw new \RuntimeException(sprintf('Le fichier est trop volumineux (max: %s).', $constraints['max_size']));
            }
        }

        // Pour maximiser la sécurité, combiner les deux méthodes :
        // Vérifier l'extension pour une première couche de filtrage.
        // Vérifier le MIME type pour une validation plus robuste du contenu.

        // vérifie l'extension
        $allowedExtensions = ['png', 'jpeg', 'jpg', 'webp'];
        $fileExtension = strtolower($file->getClientOriginalExtension());
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new \Exception('Extension de fichier non autorisée.');
        }

        // vérifie le MIME type
        $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        $fileMimeType = $file->getMimeType();
        if (!in_array($fileMimeType, $allowedMimeTypes)) {
        throw new \Exception('Type de fichier non autorisé.');
        }
    }


    // $media = $mediaUploader->upload($file, 'caption', 'alt text', MediaType::ARTICLE_IMAGE);
    // $constraints = ['max_size' => '5M'];
    // $media = $mediaUploader->upload($file, 'caption', 'alt text', MediaType::ARTICLE_IMAGE, $constraints);

}