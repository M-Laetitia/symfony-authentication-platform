<?php
namespace App\Service;

use App\Entity\Media;
use App\Enum\MediaType;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Interface MediaUploaderInterface
 *
 * Contrat pour tout service capable de gérer l’upload de fichiers.
 * Cette abstraction permet de séparer la logique métier de l’implémentation
 * concrète de l’upload, facilitant la maintenance, les tests et le remplacement
 * de la stratégie d’upload (local, cloud, S3, etc.).
 *
 * Objectifs :
 *  - Garantir une interface uniforme pour l’upload de fichiers dans l’application
 *  - Séparer les responsabilités (SRP) : le service sait uniquement gérer les fichiers
 *  - Permettre la traçabilité et l’extension future (types de médias différents)
 *
 * Usage typique :
 *  - Injecter le service dans un contrôleur ou un gestionnaire métier
 *  - Appeler upload() avec un fichier provenant d’un formulaire ou d’un import
*/

interface MediaUploaderInterface
{
    /**
     * Upload un fichier et retourne un objet Media représentant le fichier.
     *
     * Ce service encapsule la logique de stockage, de génération de métadonnées,
     * et de typage du média. La méthode ne s’occupe pas du reste du traitement métier.
     *
     * @param UploadedFile $file     Fichier à uploader
     * @param string       $caption  Légende du média (facultatif)
     * @param string       $altText  Texte alternatif pour accessibilité (facultatif)
     * @param MediaType    $type     Type de média (article_cover, gallery_image) - valeur par défaut MediaType::DEFAULT
     *
     * @return Media                Instance de Media représentant le fichier uploadé
     *
     * @throws \Exception           Si l’upload échoue (ex. problème taille, format, permissions)
     *
     * Remarques métier :
     *  - L’interface permet d’implémenter plusieurs stratégies d’upload sans modifier la couche métier
     *  - Les fichiers uploadés peuvent être validés, renommés, déplacés ou traités avant persistance
    */

    public function upload(
        UploadedFile $file, 
        string $caption = '', 
        string $altText = '', 
        MediaType $type = MediaType::DEFAULT->value
    ): Media;
}