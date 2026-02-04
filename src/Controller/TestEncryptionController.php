<?php

namespace App\Controller;

use App\Service\LogEncryptor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class TestEncryptionController extends AbstractController
{
    public function testEncryption(LogEncryptor $encryptor): Response
    {
        $ip = '172.18.0.1';
        $encryptedIp = $encryptor->encryptIp($ip);
        $decryptedIp = $encryptor->decryptIp($encryptedIp);

        return new Response(
            "IP originale : $ip<br>" .
            "IP chiffrée : $encryptedIp<br>" .
            "IP déchiffrée : $decryptedIp"
        );
    }
}