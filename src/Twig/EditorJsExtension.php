<?php
namespace App\Twig;

use App\Service\EditorJsRenderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class EditorJsExtension extends AbstractExtension
{
    public function __construct(
        private EditorJsRenderer $renderer // Appel du service
    ) {}
    // Enregistrer le filtre dans Twig
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'editorjs', // Nom utilisé dans Twig
                [$this, 'renderEditorJs'], // Méthode à appeler
                ['is_safe' => ['html']] // Configuration Twig, remplace le {{ article.content|editorjs|raw }}, éviter l'échappement HTML
            ),
        ];
    }

    // REçoit les données depuis Twig et appelle le service
    public function renderEditorJs(string|array|null $content): string
    {
        // Validation/préparation des données POUR Twig
        if (empty($content)) {
            return '';
        }
        
        // Si c'est déjà un array, on le convertit en JSON
        if (is_array($content)) {
            $content = json_encode($content);
        }

        // DÉLÈGUE le vrai travail au service
        return $this->renderer->render($content);
    }
}