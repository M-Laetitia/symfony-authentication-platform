<?php

namespace App\Controller;

use App\Form\ContactFormType;
use App\Service\MailerService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\CommentSecurityService;
use App\Service\SeoService;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function contact(Request $request, MailerService $mailerService, CommentSecurityService $CommentSecurityService, SeoService $seoService): Response
    {
        $form = $this->createForm(ContactFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $submittedAt = (int)$form->get('submittedAt')->getData();
            $timeCheck = $CommentSecurityService->checkSubmissionTime($submittedAt, $request);
        
            if (!$timeCheck['valid']) {

                if ($timeCheck['status'] === 'too_fast') {
                    $this->addFlash('warning', 'Please wait a few seconds before submitting.');
                    return $this->redirectToRoute('contact');
                }
            
                return $this->redirectToRoute('contact');
            }
        
            $mailerService->sendContactEmail(
                $data['name'], 
                $data['emailFrom'],    
                $data['message'],
                $data['subject']
            );

            $this->addFlash('success', 'Your message has been sent!');

            return $this->redirectToRoute('contact');
        }

        return $this->render('contact/contact.html.twig', [
            'contactForm' => $form->createView(),
            'meta_description' => $seoService ->getMetaDescription('contact'),
            'metaRobots' => $seoService ->getMetaRobots('contact'),
        ]);
    }
}