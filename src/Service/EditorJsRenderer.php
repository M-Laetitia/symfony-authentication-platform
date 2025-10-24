<?php
namespace App\Service;

use App\Repository\MediaRepository;

class EditorJsRenderer
{
    public function __construct(
        private MediaRepository $mediaRepository
    ) {}

 
    public function render(string|array $content): string
    {
        // si c'est une string JSON, on la décode
        if (is_string($content)) {
            $content = json_decode($content, true);
        }
        
        // check que c'est bien un array avec des blocks
        if (!is_array($content) || !isset($content['blocks'])) {
            return '<p>Contenu invalide</p>';
        }

        return $this->blocksToHtml($content['blocks']);
    }

    private function blocksToHtml(array $blocks): string
    {
        $html = '';
        
        foreach ($blocks as $block) {
            $html .= match($block['type'] ?? '') {
                'header' => $this->renderHeader($block['data'] ?? []),
                'paragraph' => $this->renderParagraph($block['data'] ?? []),
                'list' => $this->renderList($block['data'] ?? []),
                'image' => $this->renderImage($block['data'] ?? []),
                default => ''
            };
        }
        
        return $html;
    }

    private function renderHeader(array $data): string
    {
        $level = $data['level'] ?? 2;
        $text = $this->extractText($data['text'] ?? '');
        
        return sprintf('<h%d>%s</h%d>', $level, $text, $level);
    }

    private function renderParagraph(array $data): string
    {
        $text = $this->extractText($data['text'] ?? '');
        
        return sprintf('<p>%s</p>', $text);
    }

    private function renderList(array $data): string
    {
        if (empty($data['items'])) {
            return '';
        }
        
        $tag = ($data['style'] ?? 'unordered') === 'ordered' ? 'ol' : 'ul';
        $items = array_map(function($item) {
            // Gestion des différents formats d'items
            if (is_string($item)) {
                return '<li>' . htmlspecialchars($item) . '</li>';
            }
            
            if (is_array($item)) {
                // Si l'item a un champ 'content'
                if (isset($item['content'])) {
                    return '<li>' . htmlspecialchars($item['content']) . '</li>';
                }
                // Si l'item a un champ 'text'
                if (isset($item['text'])) {
                    return '<li>' . htmlspecialchars($item['text']) . '</li>';
                }
            }
            
            return '';
        }, $data['items']);
        
        // Filtrer les items vides
        $items = array_filter($items);
        
        if (empty($items)) {
            return '';
        }
        
        return sprintf('<%s>%s</%s>', $tag, implode('', $items), $tag);
    }

    private function renderImage(array $data): string
    {
         // Récupère l'ID de l'image depuis le JSON
        $imageId = $data['file']['id'] ?? null;

        if (!$imageId) {
            return '';
        }

        // Récupère l'image depuis la base de données
        $image = $this->mediaRepository->find($imageId);

        if (!$image) {
            return '';
        }


        $imagePath = htmlspecialchars($image->getPath(), ENT_QUOTES);
        $alt = htmlspecialchars($data['alt'] ?? '', ENT_QUOTES);
        $caption = htmlspecialchars($data['caption'] ?? '', ENT_QUOTES);
    
        $width = min($data['file']['width'] ?? 800, 800);
        $height = min($data['file']['height'] ?? 400, 800);


        return sprintf(
            '<figure><img src="%s" alt="%s" width="%d" height="%d" style="max-width: 100%%; height: auto;"><figcaption>%s</figcaption></figure>',
            $imagePath,
            $alt,
            $width,
            $height,
            $caption
        );
    }

    /**
     * Extrait le texte, qu'il soit string ou array
     */
    private function extractText(mixed $text): string
    {
        // Si c'est déjà une string
        if (is_string($text)) {
            return htmlspecialchars($text);
        }

        // Si c'est un array 
        if (is_array($text)) {
            if (isset($text['text'])) {
                return htmlspecialchars($text['text']);
            }
            // convertit en JSON pour debug
            return htmlspecialchars(json_encode($text));
        }
        
        return '';
    }
}