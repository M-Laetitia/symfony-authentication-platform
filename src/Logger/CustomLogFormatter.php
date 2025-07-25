<?php
// src/Logger/CustomLogFormatter.php

namespace App\Logger;

use Monolog\Formatter\FormatterInterface;
use Monolog\LogRecord;

class CustomLogFormatter implements FormatterInterface
{
    public function format(LogRecord $record): string
    {
        // Extraire l'IP du contexte si elle existe
        $ip = $record->context['ip'] ?? 'unknown';
        
        // Formatter la date au format souhaité
        $date = $record->datetime->format('D, d M Y H:i:s T');
        
        // Construire le message personnalisé
        return sprintf(
            "%s - [%s] - \"%s\" - %s\n",
            $ip,
            $date,
            $record->message,
            $record->context['http_info'] ?? 'GET /unknown HTTP/1.1 403'
        );
    }

    public function formatBatch(array $records): string
    {
        $formatted = '';
        foreach ($records as $record) {
            $formatted .= $this->format($record);
        }
        return $formatted;
    }
}