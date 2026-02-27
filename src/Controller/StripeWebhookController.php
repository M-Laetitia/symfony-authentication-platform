<?php

namespace App\Controller;

use Stripe\Webhook;
use App\Entity\Payment;
use App\Enum\OrderType;
use App\Service\StripeService;
use App\Enum\PaymentStatusType;
use App\Enum\PaymentProviderType;
use App\Enum\ServiceProposalType;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Stripe\Exception\SignatureVerificationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StripeWebhookController extends AbstractController
{
    #[Route('/stripe/webhook', name: 'stripe_webhook', methods: ['POST'])]
    public function handle(
        Request                $request,
        StripeService          $stripeService,
        OrderRepository        $orderRepository,
        EntityManagerInterface $em,
    ): Response {
        $payload   = $request->getContent();
        $sigHeader = $request->headers->get('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $stripeService->getWebhookSecret()
            );
        } catch (SignatureVerificationException $e) {
            return new Response('Signature invalide', Response::HTTP_BAD_REQUEST);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $orderId = $session->metadata->order_id ?? null;

            dump($session->metadata, $orderId);
            if (!$orderId) {
                return new Response('order_id manquant', Response::HTTP_BAD_REQUEST);
            }
            // if (!$orderId) {
            //     // Change le 400 en 200 temporairement pour ne pas bloquer
            //     return new Response('order_id manquant mais on continue', Response::HTTP_OK);
            // }
            

            $order = $orderRepository->find($orderId);

            if (!$order) {
                return new Response('Commande introuvable', Response::HTTP_NOT_FOUND);
            }

            // Idempotence : si un Payment existe déjà pour cette commande, on ne recrée pas
            if ($order->getPayment() !== null) {
                return new Response('OK', Response::HTTP_OK);
            }

            $payment = new Payment();
            $customerDetails = $session->customer_details;

            $payment->setBillingAddress([
                'name'    => $customerDetails->name,
                'email'   => $customerDetails->email,
                'line1'   => $customerDetails->address->line1,
                'line2'   => $customerDetails->address->line2,
                'city'    => $customerDetails->address->city,
                'postal_code' => $customerDetails->address->postal_code,
                'country' => $customerDetails->address->country,
            ]);

            $payment->setAmount($session->amount_total);           
            $payment->setCurrency($session->currency);
            $payment->setProvider(PaymentProviderType::STRIPE);
            $payment->setStatus(PaymentStatusType::PAID);
            $payment->setTransactionId($session->payment_intent);
            $payment->setPaidAt(new \DateTimeImmutable());
            $payment->setOrderProposal($order);

            $order->setStatus(OrderType::PAID);
            $serviceProposal = $order->getServiceProposal();
            $serviceProposal->setStatus(ServiceProposalType::ACCEPTED);

            $em->persist($payment);
            $em->flush();
        }

        return new Response('OK', Response::HTTP_OK);
    }
}