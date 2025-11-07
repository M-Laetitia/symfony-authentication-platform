<?php
namespace App\EventListener;

use App\Entity\Comment;
// objet fourni par Doctrine lors des événements lifecycle (prePersist, preUpdate)
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Yaml\Yaml;

class CommentModerationListener
{
    private string $pattern; // regex précompilée
    // Regex précompilée → $this->pattern est calculée une seule fois, gain de performance pour les grandes listes.

    public function __construct(string $yamlPath)
    {

        // Chargement (depuis YAML) et normalisation des mots interdits
        // Le fichier YAML est injecté via l’argument $yamlPath
        // Yaml::parseFile() → lit le fichier et retourne un tableau - ?? [] fallback pour éviter les erreurs
        $badWords = Yaml::parseFile($yamlPath)['bad_words'] ?? [];
        $normalizedWords = array_map([$this, 'normalizeText'], $badWords);

        // Précompilation de la regex
        $escapedWords = array_map('preg_quote', $normalizedWords);
        // preg_quote → sécurise chaque mot pour éviter que des caractères spéciaux soient interprétés comme regex.
        // implode('|', ...) → crée une alternance pour matcher n’importe quel mot interdit.
        // \b > seuls les mots entiers sont filtrés - i > insensible à la casse
        $this->pattern = '/\b(' . implode('|', $escapedWords) . ')\b/i';
    }

    // prePersist : méthode appelée automatiquement par Doctrine avant insertion en base (persist + flush)
    public function prePersist(LifecycleEventArgs $args): void
    {
        $comment = $args->getObject(); // récupère l'entité concernée par l'event pour ne traiter que les entités de type Comment
        // contrôle type + early return 
        if (!$comment instanceof Comment) {
            return;
        }

        // Normaliser le texte du commentaire avant filtrage
        $content = $comment->getContent();
        $normalizedContent = $this->normalizeText($content);
        
        // Appliquer le filtrage
        $filteredContent = preg_replace($this->pattern, '****', $normalizedContent);
        $comment->setContent($filteredContent);
        
    }

    function normalizeText(string $text): string {
        // translittérer” les accents avant de faire le filtrage - Convertit les caractères accentués en ASCII
        return transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0080-\uffff] remove', $text);
    }
    
}
