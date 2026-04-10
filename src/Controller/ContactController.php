<?php

namespace App\Controller;

use App\Form\ContactFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\SeoService;
use App\Service\ContactFormHandler;


class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function contact(Request $request, SeoService $seoService, ContactFormHandler $handler ): Response
    {
        $form = $this->createForm(ContactFormType::class);
        $result = $handler->handle($form, $request);
   

        if ($result['success']) {
            $this->addFlash('success', 'Your message has been sent!');
            return $this->redirectToRoute('contact');
        }

        if (($result['error'] ?? null) === 'too_fast') {
            $this->addFlash('warning', 'Please wait a few seconds before submitting.');
            return $this->redirectToRoute('contact');
        }

        return $this->render('contact/contact.html.twig', [
            'contactForm' => $form->createView(),
            'meta_description' => $seoService ->getMetaDescription('contact'),
            'metaRobots' => $seoService ->getMetaRobots('contact'),
        ]);
    }
}