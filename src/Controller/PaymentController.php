<?php
namespace App\Controller;

use App\Entity\Order;
use App\Enum\OrderType;
use App\Entity\ServiceProposal;
use App\Enum\ServiceProposalType;
use App\Form\OrderConfirmationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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

            // Create Order
            $order = new Order();
            $order->setCreatedAt(new \DateTimeImmutable());
            $order->setStatus(OrderType::PENDING); 
            $order->setServiceProposal($proposal);
            $priceTtc = $proposal->getPriceExcluTax() * (1 + $proposal->getTax()->getRate());

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
            $proposalId = $proposal->getId();
            do {
                $orderNumber = sprintf('ORD-%s-%04d', date('Y'), $proposalId);

                $existing = $em->getRepository(Order::class)->findOneBy([
                    'order_number' => $orderNumber,
                ]);

                if ($existing) {
                    $proposalId++;
                }
            } while ($existing);

            $order->setOrderNumber($orderNumber);
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

            return $this->redirectToRoute('payment_new', [
                'id' => $order->getId()
            ]);
        }



        return $this->render('payment/order/new.html.twig', [
            // 'order' => $order,
            'proposal' => $proposal,
            'user_id' => $userId,
            'form' => $form, 
        ]);
    }
}
