<?php
namespace App\Service;

use Symfony\Component\Yaml\Yaml;

class ErrorManager
{
    private array $errors;

    public function __construct(string $projectDir)
    {
        $path = $projectDir . '/config/errors.yaml';
        $data = Yaml::parseFile($path);
        $this->errors = $data['errors'] ?? [];
    }

    public function getError(int $code): array
    {
        return $this->errors[$code] ?? [
            'name' => 'Unknown Error',
            'type' => 'Error',
            'message' => 'An unexpected error occurred.',
            'redirect' => 'app_home',
        ];
    }
}