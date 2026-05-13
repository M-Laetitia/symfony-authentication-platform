<?php

namespace App\Controller;

use App\Service\ErrorMessageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ErrorController extends AbstractController
{
    public function __construct(private ErrorMessageService $errorMessageService)
    {
    }

    #[Route(path: '/_error/{statusCode}', name: 'error', requirements: ['statusCode' => '\d+'])]
    public function show(FlattenException $exception): Response
    {
        $statusCode = $exception->getStatusCode();
        $errorData = $this->errorMessageService->getMessage($statusCode);

        return $this->render('bundles/TwigBundle/Exception/error.html.twig', [
            'status_code' => $statusCode,
            'title' => $errorData['title'],
            'description' => $errorData['description'],
            'exception' => $exception,
        ]);
    }

    // #[Route(path: '/error-preview', name: 'error_preview')]
    // public function preview(): Response
    // {
    //     return $this->render('bundles/TwigBundle/Exception/error.html.twig', [
    //         'status_code' => 404,
    //         'title' => 'Page Not Found',
    //         'description' => 'The page you\'re looking for doesn\'t exist. It might have been moved or deleted.',
    //     ]);
    // }
}
 

