<?php
namespace App\Controller;

use App\Entity\Order;
use App\Enum\OrderType;
use App\Service\StripeService;
use App\Entity\ServiceProposal;
use App\Enum\ServiceProposalType;
use App\Repository\OrderRepository;
use App\Repository\InvoiceRepository;
use App\Form\OrderConfirmationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PaymentController extends AbstractController
{
    #[Route('/order/create/{id}', name: 'create_order', methods: ['GET', 'POST'])]
    public function review(
        ServiceProposal $proposal,
        Request $request,
        Security $security,
        EntityManagerInterface $em
    ): Response {

        /** @var \App\Entity\User $user */
        $user = $security->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }
        $userId = $user->getId();

        if ($proposal->getClient() !== $user) {
            throw $this->createAccessDeniedException();
        }
        if (!in_array($proposal->getStatus()->value, ['pending', 'awaiting_payment'])) {
            throw $this->createAccessDeniedException();
        }
        if ($proposal->getExpirationDate() < new \DateTime()) {
            throw $this->createAccessDeniedException('Proposal expired');
        }


        // Create the form
        $form = $this->createForm(OrderConfirmationFormType::class);
        $form->handleRequest($request);

        // If form submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $security->getUser();
            // Create Order
            $order = new Order();
            $order->setCreatedAt(new \DateTimeImmutable());
            $order->setStatus(OrderType::PENDING); 
            $order->setServiceProposal($proposal);
            $order->setClient($user);
            $priceTtc = $proposal->getPriceExcluTax() * (1 + $proposal->getTax()->getRate());
            $order->setTotalAmount((int) round($priceTtc * 100));

            $snapshot = [
                'title' => $proposal->getTitle(),
                'message' => $proposal->getMessage(),
                'photographer_firstname' => $proposal->getPhotographer()->getFirstName(),
                'photographer_lastname' => $proposal->getPhotographer()->getLastName(),
                'price_ht' => $proposal->getPriceExcluTax(),
                'price_ttc' => $priceTtc,
                'expiration_date' => $proposal->getExpirationDate()?->format('Y-m-d H:i:s'),
                'proposal_created_at' => $proposal->getCreatedAt()?->format('Y-m-d H:i:s'),
            ];

            $order->setServiceSnapshot($snapshot);
            $order->setTermsAcceptedAt(new \DateTimeImmutable());
            $data = $form->getData();
            $order->setNote($data['note'] ?? null);

            $proposal->setStatus(ServiceProposalType::AWAITING_PAYMENT);
            
            // $idOrder = $order->getId();
            // $proposalId = $proposal->getId();
            // do {
            //     $orderNumber = sprintf('ORD-%s-%04d', date('Y'), $proposalId);

            //     $existing = $em->getRepository(Order::class)->findOneBy([
            //         'order_number' => $orderNumber,
            //     ]);

            //     if ($existing) {
            //         $proposalId++;
            //     }
            // } while ($existing);

            $order->setOrderNumber(
                sprintf('ORD-%s-%05d', (new \DateTimeImmutable())->format('Y'), $proposal->getId())
            );
    

            // $order->setOrderNumber($orderNumber);
            $em->persist($order);
            $em->flush();

            $this->addFlash('success', 'Your order has been successfully confirmed.');


            // Vérifier que l'Order appartient bien à l'utilisateur
            // if ($order->getServiceProposal()->getClient() !== $user) {
            //     throw $this->createAccessDeniedException();
            // }

            // // Vérifier que le Proposal correspond à l'Order
            // $canProceedToPayment = false;
            // if ($proposal && $order->getServiceProposal()->getId() === $proposal->getId()) {
            //     $canProceedToPayment = true;
            // }

            return $this->redirectToRoute('payment_page', [
                'orderId' => $order->getId()
            ]);
     
        }


        return $this->render('payment/order/new.html.twig', [
            'proposal' => $proposal,
            'user_id' => $userId,
            'form' => $form->createView(),
        ]);
    }


    // #[Route('/order/{orderId}/proceed-to-payment', name: 'payment_redirect', methods: ['POST'])]
    // public function redirectToPayment(Order $order, Security $security,): Response
    // {

    //     /** @var \App\Entity\User $user */
    //     $user = $security->getUser();
    
    //     if ($order->getServiceProposal()->getClient() !== $user) {
    //         throw $this->createAccessDeniedException();
    //     }
    
    //     if ($order->getServiceProposal()->getStatus()->value !== 'awaiting_payment') {
    //         $this->addFlash('error', 'Payment is not allowed for this order.');
    //         return $this->redirectToRoute('order_show', ['id' => $order->getId()]);
    //     }
    
      
    // }


    #[Route('/payment/{orderId}', name: 'payment_page', methods: ['GET'])]
    public function showPaymentPage(
        int $orderId,
        Request $request,
        Security $security,
        EntityManagerInterface $em
    ): Response {
        $user = $security->getUser();

        $order = $em->getRepository(Order::class)->find($orderId);
        if (!$order) {
            throw $this->createNotFoundException('Order not found.');
        }

        if ($order->getServiceProposal()->getClient() !== $user) {
            throw $this->createAccessDeniedException('You are not allowed to pay this order.');
        }

        if ($order->getServiceProposal()->getStatus()->value !== 'awaiting_payment') {
            $this->addFlash('error', 'This order is not ready for payment.');
            return $this->redirectToRoute('order_show', ['id' => $order->getId()]);
        }

        return $this->render('payment/payment.html.twig', [
            'order' => $order,
            'proposal' => $order->getServiceProposal(),
            'user' => $user,
        ]);
    }

    #[Route('/order/{id}/checkout', name: 'payment_checkout', methods: ['POST'])]
    public function checkout(
        int               $id,
        OrderRepository   $orderRepository,
        StripeService     $stripeService,
    ): Response {
        $order = $orderRepository->find($id);

        if (!$order) {
            throw $this->createNotFoundException('Commande introuvable');
        }

        // Sécurité : vérifier que la commande appartient à l'utilisateur connecté
        if ($order->getServiceProposal()->getClient() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        
        $session = $stripeService->createCheckoutSession(
            amount:      $order->getTotalAmount(),         
            currency:    'eur',
            description: 'Commande #' . $order->getId(),
            successUrl:  $this->generateUrl('payment_success', ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            cancelUrl:   $this->generateUrl('payment_cancel',  ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            orderId:     $order->getId(),
        );
        
        // code 303 est requis par Stripe pour les redirections POST
        return $this->redirect($session->url, 303);
    }

    #[Route('/order/{id}/payment/success', name: 'payment_success')]
    public function success(int $id, OrderRepository $orderRepository): Response
    {

        $order   = $orderRepository->find($id);
        if (!$order) {
            throw $this->createNotFoundException();
        }
            // Vérifier que la commande appartient à l'utilisateur connecté
        if ($order->getServiceProposal()->getClient() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $invoice = $order->getInvoice();
        // Page affichée après paiement réussi
        // Ne pas mettre à jour la BDD ici - cest le webhook qui fait foi
        return $this->render('payment/success.html.twig', [
        'orderId' => $id,
        'invoice' => $invoice,
    ]);
    }

    #[Route('/order/{id}/payment/cancel', name: 'payment_cancel')]
    public function cancel(int $id): Response
    {
        return $this->render('payment/cancel.html.twig', ['orderId' => $id]);
    }

    #[Route('/invoice/{id}/download', name: 'invoice_download')]
    public function downloadInvoice(
        int                $id,
        InvoiceRepository  $invoiceRepository
    ): Response {
        $invoice = $invoiceRepository->find($id);
    
        if (!$invoice) {
            throw $this->createNotFoundException();
        }
    
        // Sécurité : vérifier que la facture appartient à l'utilisateur connecté
        if ($invoice->getOrderProposal()->getServiceProposal()->getClient()!== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
    
        $path = dirname(__DIR__, 2) . '/' . $invoice->getPdfPath();
    
        if (!file_exists($path)) {
            throw $this->createNotFoundException('PDF introuvable');
        }
    
        return new Response(
            file_get_contents($path),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
            ]
        );
    }
}
