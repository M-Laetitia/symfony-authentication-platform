<?php
namespace App\Service;

class EditorJsRenderer
{
 
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
        if (empty($data['file']['url'])) {
            return '';
        }
        
        return sprintf(
            '<figure><img src="%s" alt="%s"><figcaption>%s</figcaption></figure>',
            htmlspecialchars($data['file']['url']),
            htmlspecialchars($data['caption'] ?? ''),
            htmlspecialchars($data['caption'] ?? '')
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