<?php

namespace App\Service;

use App\Entity\Invoice;
use App\Entity\Order;
use App\Entity\Payment;
use App\Enum\InvoiceType;
use Doctrine\ORM\EntityManagerInterface;

class InvoiceService
{
    public function __construct(
        private EntityManagerInterface $em,
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
            sprintf('ORD-%s-%05d', (new \DateTimeImmutable())->format('Y'), $order->getId())
        );

        // $invoice->setInvoiceNumber(sprintf('INV-%s-%05d', (new \DateTimeImmutable())->format('Y'), $order->getId()));

        $invoice->setBillingAddress($payment->getBillingAddress() ?? []);

        // $user = $order->getClient();
        // $invoice->setBuyerSnapshot([
        //     'firstName' => $user->getFirstName(),
        //     'lastName'  => $user->getLastName(),
        //     'email'     => $user->getEmail(),
        // ]);

        $photographer = $order->getServiceProposal()->getPhotographer();
        $invoice->setSellerSnapshot([
            'firstName' => $photographer->getFirstName(),
            'lastName'  => $photographer->getLastName(),
        ]);

        $this->em->persist($invoice);

        return $invoice;
    }

}