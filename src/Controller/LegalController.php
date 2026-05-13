<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LegalController extends AbstractController
{
    #[Route('/terms-and-conditions', name: 'legal_terms')]
    public function terms(): Response
    {
        return $this->render('legal_pages/terms.html.twig');
    }

    #[Route('/legal-notice', name: 'legal_notice')]
    public function legalNotice(): Response
    {
        return $this->render('legal_pages/legal_notice.html.twig');
    }

    #[Route('/privacy-policy', name: 'legal_privacy')]
    public function privacyPolicy(): Response
    {
        return $this->render('legal_pages/privacy_policy.html.twig');
    }

    #[Route('/sales-conditions', name: 'legal_cgv')]
    public function salesConditions(): Response
    {
        return $this->render('legal_pages/cgv.html.twig');
    }
}
