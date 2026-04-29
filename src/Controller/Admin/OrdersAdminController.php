<?php

namespace App\Controller\Admin;

use App\Enum\ServiceProposalType;
use App\Repository\InvoiceRepository;
use App\Repository\OrderRepository;
use App\Repository\PaymentRepository;
use App\Repository\ServiceProposalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;



#[IsGranted('ROLE_PHOTOGRAPHER')]
#[Route('/admin/orders')]
class OrdersAdminController extends AbstractController
{
    #[Route('/', name: 'admin_orders_index')]
    public function index(
        ServiceProposalRepository $serviceProposalRepository,
        OrderRepository $orderRepository,
        PaymentRepository $paymentRepository,
        InvoiceRepository $invoiceRepository
    ): Response {
        $serviceProposals = $serviceProposalRepository->findAll();
        $orders = $orderRepository->findAll();
        $payments = $paymentRepository->findAll();
        $invoices = $invoiceRepository->findAll();

        return $this->render('admin/orders/index.html.twig', [
            'serviceProposals' => $serviceProposals,
            'orders' => $orders,
            'payments' => $payments,
            'invoices' => $invoices,
        ]);
    }
    #[Route('/proposal/{id}/cancel', name: 'admin_proposal_cancel', methods: ['POST'])]
    public function cancelProposal(
        int $id,
        ServiceProposalRepository $serviceRepo,
        EntityManagerInterface $em,
        Request $request
    ): Response {
        $proposal = $serviceRepo->find($id);
        if (!$proposal) {
            $this->addFlash('danger', 'Proposal not found.');
            return $this->redirectToRoute('admin_orders_index');
        }
        // Optionally: check user is allowed to cancel
        $proposal->setStatus(ServiceProposalType::CANCELLED);
        $em->flush();
        $this->addFlash('success', 'Proposal cancelled.');
        if ($request->request->get('from_conversation')) {
            // Redirige vers la page de la conversation
            $conversationId = $proposal->getConversation()->getId();
            return $this->redirectToRoute('chat_show', ['id' => $conversationId]);
        }
        return $this->redirectToRoute('admin_orders_index');
    }
}
