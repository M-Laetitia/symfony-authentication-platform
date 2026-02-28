<?php

namespace App\Service;

use Mpdf\Mpdf;
use Twig\Environment;
use App\Entity\Order;
use App\Entity\Invoice;
use App\Entity\Payment;
use App\Enum\InvoiceType;
use Doctrine\ORM\EntityManagerInterface;

class InvoiceService
{
    public function __construct(
        private EntityManagerInterface $em,
        private Environment $twig,
    ) {}

    public function createFromOrder(Order $order, Payment $payment): Invoice
    {
        $invoice = new Invoice();

        $invoice->setIssuedAt(new \DateTimeImmutable());
        $invoice->setStatus(InvoiceType::ISSUED);
        $invoice->setTotalAmount($payment->getAmount());
        $invoice->setOrderProposal($order);
        $invoice->setPdfPath('');
        $invoice->setIsArchived(false);

        $invoice->setInvoiceNumber(
            sprintf('INV-%s-%05d', (new \DateTimeImmutable())->format('Y'), $order->getId())
        );

        // 

        // $invoice->setBillingAddress($payment->getBillingAddress() ?? []);

        // $user = $order->getClient();
        // $invoice->setBuyerSnapshot([
        //     'firstName' => $user->getFirstName(),
        //     'lastName'  => $user->getLastName(),
        //     'email'     => $user->getEmail(),
        // ]);



        // ^ Buyer snapshot
        // $invoice->setBuyerSnapshot($payment->getBillingAddress() ?? []);
        $invoice->setBuyerSnapshot($payment->getBillingAddress() ?? []);

        // ^ Payment snapshot 
        $invoice->setPaymentSnapshot([
            'provider'      => $payment->getProvider()->value,
            'transactionId' => $payment->getTransactionId(),
            'currency'      => $payment->getCurrency(),
            'amount'        => $payment->getAmount(),
            'paidAt'        => $payment->getPaidAt()->format('d/m/Y H:i'),
        ]);

        // ^ Seller snapshot
        $photographer = $order->getServiceProposal()->getPhotographer();
        $invoice->setSellerSnapshot([
            'firstName' => $photographer->getFirstName(),
            'lastName'  => $photographer->getLastName(),
        ]);

        // ^ Order snapshot
        $serviceSnapshot = $order->getServiceSnapshot();
        $invoice->setOrderSnapshot([
            'title'       => $serviceSnapshot['title'],
            'description' => $serviceSnapshot['message'],
            'price_ht'    => $serviceSnapshot['price_ht'],
            'price_ttc'   => $serviceSnapshot['price_ttc'],
            'created_at'  => $order->getCreatedAt()->format('d/m/Y'),
            'orderTotal'       => $order->getTotalAmount(),
        ]);

        $pdfPath = $this->generatePdf($invoice);
        $invoice->setPdfPath($pdfPath);

        $this->em->persist($invoice);

        return $invoice;
    }


    private function generatePdf(Invoice $invoice): string
    {
        $year = $invoice->getIssuedAt()->format('Y');
        $dir  = dirname(__DIR__, 2) . '/storage/invoices/' . $year;

        // Crée le dossier de l'année s'il n'existe pas
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        // $filename = $invoice->getInvoiceNumber() . '.pdf';
        $filename = 'INVOICE-' . $invoice->getIssuedAt()->format('Y') . '-' . $invoice->getId() . '.pdf';

        $path     = $dir . '/' . $filename;

        $html = $this->twig->render('pdf/invoice.html.twig', [
            'invoice_number' => $invoice->getInvoiceNumber(),
            // 'issued_at'      => $invoice->getIssuedAt()->format('d/m/Y'),
            // 'seller'         => $invoice->getSellerSnapshot(),
            // 'buyer'          => $invoice->getBuyerSnapshot(),
            // 'description'    => $invoice->getOrderSnapshot()['title'],
            // 'service_date'   => $invoice->getOrderSnapshot()['created_at'],
            // 'amount_ht'      => number_format($invoice->getOrderSnapshot()['price_ht'], 2, ',', ' '),
            // 'amount_ttc'     => number_format($invoice->getOrderSnapshot()['price_ttc'], 2, ',', ' '),
            // 'payment_method' => $invoice->getPaymentSnapshot()['provider'],
            // 'paid_at'        => $invoice->getPaymentSnapshot()['paidAt'],
            // 'transaction_id' => $invoice->getPaymentSnapshot()['transactionId'],
        ]);

        $mpdf = new Mpdf(['tempDir' => dirname(__DIR__, 2) . '/var/mpdf']);
        $mpdf->WriteHTML($html);
        $mpdf->Output($path, 'F');

        return 'storage/invoices/' . $year . '/' . $filename;
    }

}