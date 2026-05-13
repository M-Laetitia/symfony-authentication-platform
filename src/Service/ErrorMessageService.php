<?php

namespace App\Service;

class ErrorMessageService
{
    private array $messages = [
        400 => ['title' => 'Bad Request', 'description' => 'The request you made is invalid. Please check the data and try again.'],
        401 => ['title' => 'Unauthorized', 'description' => 'You need to be logged in to access this page.'],
        403 => ['title' => 'Forbidden', 'description' => 'You don\'t have permission to access this page.'],
        404 => ['title' => 'Page Not Found', 'description' => 'Sorry, we couldn\'t find the page you were looking for.'],
        408 => ['title' => 'Request Timeout', 'description' => 'Your request took too long. Please try again.'],
        410 => ['title' => 'Gone', 'description' => 'This resource is no longer available.'],
        429 => ['title' => 'Too Many Requests', 'description' => 'You\'re making requests too fast. Please slow down and try again.'],
        500 => ['title' => 'Server Error', 'description' => 'Something went wrong on our end. Please try again later.'],
        502 => ['title' => 'Bad Gateway', 'description' => 'The server is temporarily unavailable. Please try again later.'],
        503 => ['title' => 'Service Unavailable', 'description' => 'The server is undergoing maintenance. Please try again later.'],
        504 => ['title' => 'Gateway Timeout', 'description' => 'The server took too long to respond. Please try again later.'],
    ];

    public function getMessage(int $statusCode): array
    {
        return $this->messages[$statusCode] ?? $this->messages[500];
    }

    public function isSupported(int $statusCode): bool
    {
        return isset($this->messages[$statusCode]);
    }
}
