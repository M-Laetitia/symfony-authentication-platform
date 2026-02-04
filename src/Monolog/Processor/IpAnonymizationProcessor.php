<?php

namespace App\Monolog\Processor;

use App\Service\LogEncryptor;
use Monolog\LogRecord;

/**
 * Processor Monolog pour anonymiser les IPs dans les logs.
 *
 * Un "processor" Monolog est une classe qui modifie les enregistrements de log
 * avant qu'ils ne soient écrits - ici il s'agit de chiffrer les adresse IP.
 */
class IpAnonymizationProcessor
{
    private LogEncryptor $encryptor;

    public function __construct(LogEncryptor $encryptor)
    {
        $this->encryptor = $encryptor;
    }

    /**
     * Méthode magique __invoke() : appelée automatiquement par Monolog.
     *
     * @param LogRecord $record
     * @return array LogRecord
     */

     public function __invoke(LogRecord $record): LogRecord
     {
         // Crée une copie du contexte pour éviter de modifier l'objet readonly
         $context = $record->context;
 
         // Vérifie si 'ip' existe dans le contexte
         if (isset($context['ip'])) {
            //  $context['_debug'] = 'IP_PROCESSOR_PASSED_HERE';
             // Chiffre l'IP si elle n'est pas déjà chiffrée
             if (!preg_match('/^[a-f0-9]{64,}$/', $context['ip'])) {
                 $context['ip'] = $this->encryptor->encryptIp($context['ip']);
             }
         }
 
         // Retourne un nouvel objet LogRecord avec le contexte modifié
         return new LogRecord(
             $record->datetime,
             $record->channel,
             $record->level,
             $record->message,
             $context,
             $record->extra,
             $record->formatted
         );
     }
}