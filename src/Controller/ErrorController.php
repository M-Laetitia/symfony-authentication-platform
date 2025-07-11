<?php


// src/Controller/ErrorController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ErrorController extends AbstractController
{
    public function show(Request $request): Response
    {
        $exception = $request->attributes->get('exception');

        $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;

        if ($statusCode === 403) {
            return $this->render('error/error403.html.twig');
        }

        return $this->render('error/error.html.twig', ['status_code' => $statusCode]);
    }
}
 
