<?php

namespace App\Service;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

class LogEncryptor
{
    private $key;

    public function __construct(string $encryptionKey)
    {
        $this->key = Key::loadFromAsciiSafeString($encryptionKey);
    }

    // Chiffre une IP
    public function encryptIp(string $ip): string
    {
        return Crypto::encrypt($ip, $this->key);
    }

    // Déchiffre une IP
    public function decryptIp(string $encryptedIp): string
    {
        return Crypto::decrypt($encryptedIp, $this->key);
    }
}