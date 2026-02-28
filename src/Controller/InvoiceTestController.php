<?php

namespace App\Controller;

use Mpdf\Mpdf;
use Twig\Environment;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class InvoiceTestController extends AbstractController
{
    // #[Route('/test/invoice', name: 'test_invoice')]
    // public function index(Environment $twig): Response
    // {
    //     $html = $twig->render('pdf/invoice.html.twig', [
    //         'invoice_number' => 'ORD-2026-00012',
    //         'issued_at'      => '27/02/2026',
    //         'buyer'         => [
    //             'name'        => 'Jean Dupont',
    //             'line2'        => '',
    //             'line1'       => '12 rue de la Paix',
    //             'city'        => 'Paris',
    //             'postal_code' => '75001',
    //             'country'     => 'FR',
    //             'email'     => 'exemple@exemple.com',
    //         ],
    //         'seller'        => [
    //             'firstName' => 'Marie',
    //             'lastName'  => 'Martin',
    //             'address'  => '12 rue principale',
    //             'postal_code'  => '67200',
    //             'city'  => 'Strasbourg',
    //             'siret'  => 'z415454ddfdee',
    //         ],
    //         'description'   => 'Séance photo portrait',
    //         'amount_ht'        => '150,00',
    //         'amount_ttc'        => '225,00',
    //         'paid_at'        => '27/02/2026',
    //         'payment_method'        => 'Stripe - carte bancaire',
            
    //     ]);

    //     $mpdf = new Mpdf([
    //         'tempDir' => __DIR__ . '/../../var/mpdf'
    //     ]);
    //     $mpdf->WriteHTML($html);

    //     $pdfContent = $mpdf->Output('facture.pdf', 'S');

    //     return new Response($pdfContent, 200, [
    //         'Content-Type'        => 'application/pdf',
    //         'Content-Disposition' => 'inline; filename="facture.pdf"',
    //     ]);
    // }

    // #[Route('/test/invoice', name: 'test_invoice')]
    // public function index(): Response
    // {
    //     return $this->render('pdf/invoice.html.twig', [
    //         'invoice_number' => 'ORD-2026-00012',
    //         'issued_at'      => '27/02/2026',

    //         'seller' => [
    //             'firstName'   => 'Marie',
    //             'lastName'    => 'Martin',
    //             'address'     => '5 rue de la Photographie',
    //             'postal_code' => '75010',
    //             'city'        => 'Paris',
    //             'siret'       => '123 456 789 00012',
    //             'vat_number'  => null,
    //         ],

    //         'buyer' => [
    //             'name'        => 'Jean Dupont',
    //             'line1'       => '12 rue de la Paix',
    //             'line2'       => null,
    //             'city'        => 'Lyon',
    //             'postal_code' => '69001',
    //             'country'     => 'France',
    //             'email'       => 'jean.dupont@email.com',
    //         ],

    //         'description'  => 'Séance photo portrait',
    //         'service_date' => '25/02/2026',
    //         'amount_ht'    => '150,00',
    //         'amount_ttc'   => '150,00',

    //         'payment_method' => 'Stripe (carte bancaire)',
    //         'paid_at'        => '27/02/2026 à 09h54',
    //         'transaction_id' => 'pi_3T5MwkFInhPlxmzG0143IaLc',
    //     ]);
    // }


}