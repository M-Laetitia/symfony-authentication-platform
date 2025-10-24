<?php
namespace App\Service;

use App\Entity\Media;
use App\Enum\MediaType;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface MediaUploaderInterface
{
    /**
     * Upload un fichier et retourne un Media.
     */
    public function upload(UploadedFile $file, string $caption = '', string $altText = '', MediaType $type = MediaType::DEFAULT->value): Media;
}