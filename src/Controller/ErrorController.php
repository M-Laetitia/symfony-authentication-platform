<?php


// src/Controller/ErrorController.php
namespace App\Controller;

use App\Service\ErrorManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

// class ErrorController extends AbstractController
// {
//     public function show(Request $request): Response
//     {
//         $exception = $request->attributes->get('exception');

//         $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;

//         if ($statusCode === 403) {
//             return $this->render('error/error403.html.twig');
//         }

//         return $this->render('error/error.html.twig', ['status_code' => $statusCode]);
//     }
// }
 

class ErrorController extends AbstractController
{
    private ErrorManager $errorManager;

    public function __construct(ErrorManager $errorManager)
    {
        $this->errorManager = $errorManager;
    }

    public function show(?\Throwable $exception = null): Response
    {
        $code = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;
        $errorData = $this->errorManager->getError($code);

        return $this->render('error/custom_error.html.twig', [
            'error' => $errorData
        ]);
    }
}