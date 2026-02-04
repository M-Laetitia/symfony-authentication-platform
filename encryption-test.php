<?php
require __DIR__ . '/vendor/autoload.php';

use App\Service\LogEncryptor;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->load(__DIR__.'/.env');

$encryptor = new LogEncryptor($_ENV['LOG_ENCRYPTION_KEY']);

$ip = '185.18.0.1';
$encrypted = $encryptor->encryptIp($ip);
$decrypted = $encryptor->decryptIp($encrypted);

echo "IP originale : " . $ip . "\n";
echo "IP chiffrée : " . $encrypted . "\n";
echo "IP déchiffrée : " . $decrypted . "\n";
