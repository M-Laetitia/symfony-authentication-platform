<?php 

namespace App\Service;

use App\Entity\Media;
use App\Enum\MediaType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Psr\Log\LoggerInterface;

class MediaUploader implements MediaUploaderInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private string $uploadsDir,
        private FilterManager $filterManager,
        private CacheManager $cacheManager,
        private DataManager $dataManager,
        private LoggerInterface $logger
    ) {}

    public function upload(
        UploadedFile $file,
        string $caption = '',
        string $altText = '',
        MediaType $type = MediaType::DEFAULT,
        ?string $subfolder = null,
        array $constraints = []
        ): Media {

        $defaultConstraints = [
            'max_size' => '3M',
            'allowed_types' => ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'],
        ];
        $constraints = array_merge($defaultConstraints, $constraints);

        // 1 Validation du fichier
        $this->validateFile($file, $constraints);

        // 2️ Détermination du dossier de destination
        $uploadDir = $this->uploadsDir;
        if ($subfolder) {
            $uploadDir .= '/' . trim($subfolder, '/');
        }
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
            throw new HttpException(500, "Impossible de créer le répertoire d’upload : $uploadDir");
        }

        $this->logger->info("Upload dans le dossier : $uploadDir");

        // 3️ Sauvegarde temporaire du fichier original
        $originalExt = $file->guessExtension();
        $basename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $tempFilename = $basename . '.' . $originalExt;
        $absoluteTempPath = $uploadDir . '/' . $tempFilename;

        try {
            $file->move($uploadDir, $tempFilename);
        } catch (\Throwable $e) {
            throw new HttpException(500, "Échec du déplacement du fichier : " . $e->getMessage());
        }

        if (!file_exists($absoluteTempPath)) {
            throw new HttpException(500, "Fichier temporaire introuvable après le move.");
        }

        $this->logger->info("Fichier temporaire déplacé : $absoluteTempPath");

        $absoluteWebpPath = $this->generateWebp($absoluteTempPath, $uploadDir, $type);

        //  Récupération des dimensions
        // [$width, $height] = @getimagesize($absoluteWebpPath) ?: [0, 0];
        // if ($width === 0 || $height === 0) {
        //     @unlink($absoluteWebpPath);
        //     throw new HttpException(500, "Impossible de lire les dimensions de l’image WebP.");
        // }

        $imageInfo = getimagesize($absoluteWebpPath);
        $width = $imageInfo[0] ?? 800;
        $height = $imageInfo[1] ?? 400;

        $relativeWebpPath = str_replace($this->uploadsDir . '/', '', $absoluteWebpPath);
        // Ajouter le préfixe /uploads/ pour que le path soit absolu du serveur
        $relativeWebpPath = '/uploads/' . $relativeWebpPath;

        $this->em->beginTransaction(); // démarre la transaction 
        // démarre une transaction de base de données, ce qui signifie que toutes les opérations suivantes (insert, update, delete) ne seront pas réellement validées tant que que commit n'est pas appelé.
        try {
            $media = new Media();
            $media->setPath($relativeWebpPath);
            $media->setCaption($caption);
            $media->setAltText($altText);
            $media->setType($type);
            $media->setWidth($width);
            $media->setHeight($height);

            $this->em->persist($media);
            $this->em->flush();
            $this->em->commit(); // valide définitivement 

            $this->logger->info("Image WebP enregistrée avec succès : $relativeWebpPath");

            return $media;
        } catch (\Throwable $e) {
            $this->em->rollback(); // annule tout depuis le début de la transaction
            @unlink($absoluteWebpPath);
            throw new HttpException(500, "Erreur lors de la sauvegarde en base : " . $e->getMessage());
        }
    }

    private function validateFile(UploadedFile $file, array $constraints): void
    {
        // Check the max size constraint
        if (isset($constraints['max_size'])) {
            $maxSize = $constraints['max_size'];
    
            // If $maxSize is a string like '3M', convert it to bytes
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
                throw new \RuntimeException(sprintf('The file is too large (max: %s).', $constraints['max_size']));
            }
        }

        // Pour maximiser la sécurité, combiner les deux méthodes :
        // Vérifier l'extension pour une première couche de filtrage.
        // Vérifier le MIME type pour une validation plus robuste du contenu.

        // Check extension
        $allowedExtensions = ['png', 'jpeg', 'jpg', 'webp'];
        $fileExtension = strtolower($file->getClientOriginalExtension());
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new \Exception('Unsupported file extensions');
        }

        // Check MIME type
        $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        $fileMimeType = $file->getMimeType();
        if (!in_array($fileMimeType, $allowedMimeTypes)) {
            throw new \Exception('Unsupported file type.');
        }
    }

    private function generateWebp(string $absoluteTempPath, string $uploadDir, MediaType $type): string
    {
        $filterName = 'webp_upload';
        $relativePath = str_replace($this->uploadsDir . '/', '', $absoluteTempPath);
        $relativePath = ltrim($relativePath, '/');

        try {
            $binary = $this->dataManager->find($filterName, $relativePath);
            $filteredBinary = $this->filterManager->applyFilter($binary, $filterName);
        } catch (\Throwable $e) {
            @unlink($absoluteTempPath);
            throw new HttpException(500, "Erreur LiipImagine lors de la génération WebP : " . $e->getMessage());
        }

        $prefix = $type instanceof MediaType ? $type->value : 'media';
        $random = bin2hex(random_bytes(16));
        $webpFilename = $prefix . '_' . $random . '.webp';
        $absoluteWebpPath = $uploadDir . '/' . $webpFilename;

        try {
            file_put_contents($absoluteWebpPath, $filteredBinary->getContent());
        } catch (\Throwable $e) {
            @unlink($absoluteTempPath);
            throw new HttpException(500, "Échec de l’écriture du fichier WebP : " . $e->getMessage());
        }

        if (!file_exists($absoluteWebpPath)) {
            @unlink($absoluteTempPath);
            throw new HttpException(500, "Le fichier WebP n’a pas été généré correctement.");
        }

        // Suppression du fichier temporaire
        @unlink($absoluteTempPath);

        // mise en cache immédiate :
        $this->copyToCache($absoluteWebpPath, $filterName);

        return $absoluteWebpPath;
    }

    private function copyToCache(string $absoluteWebpPath, string $filterName): void
    {
        $relativePath = str_replace($this->uploadsDir . '/', '', $absoluteWebpPath);
        $relativePath = 'uploads/' . ltrim($relativePath, '/');

        try {
            $cacheFile = $this->uploadsDir . '/../media/cache/' . $filterName . '/' . $relativePath;
            $cacheDir = dirname($cacheFile);
            
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0777, true);
            }
            
            copy($absoluteWebpPath, $cacheFile);

            // dd([
            //     'absoluteWebpPath' => $absoluteWebpPath,
            //     'absoluteWebpPath_exists' => file_exists($absoluteWebpPath),
            //     'relativePath' => $relativePath,
            //     'cacheFile' => $cacheFile,
            //     'cacheDir' => $cacheDir,
            //     'cacheDir_exists' => is_dir($cacheDir),
            //     'cacheFile_exists' => file_exists($cacheFile),
            //     'copy_success' => file_exists($cacheFile) && filesize($cacheFile) > 0
            // ]);
        } catch (\Exception $e) {
            dd('ERREUR:', $e->getMessage());
        }
    }




    // $media = $mediaUploader->upload($file, 'caption', 'alt text', MediaType::ARTICLE_IMAGE);
    // $constraints = ['max_size' => '5M'];
    // $media = $mediaUploader->upload($file, 'caption', 'alt text', MediaType::ARTICLE_IMAGE, $constraints);

}