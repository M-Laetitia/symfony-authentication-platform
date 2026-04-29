<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;

#[IsGranted('ROLE_ADMIN')]
#[IsGranted('ROLE_PHOTOGRAPHER')]
class InvoiceFileController extends AbstractController
{
    #[Route('/admin/invoice-file/{year}/{filename}', name: 'admin_invoice_file', requirements: ['year' => '\\d{4}', 'filename' => '.+\\.pdf$'])]
    public function serveInvoice(string $year, string $filename, Request $request): BinaryFileResponse
    {
        $filePath = $this->getParameter('kernel.project_dir') . "/storage/invoices/{$year}/{$filename}";
        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Invoice file not found');
        }
        $response = new BinaryFileResponse($filePath);
        $disposition = $request->query->get('inline') ? ResponseHeaderBag::DISPOSITION_INLINE : ResponseHeaderBag::DISPOSITION_ATTACHMENT;
        $response->setContentDisposition(
            $disposition,
            $filename
        );
        return $response;
    }
}
