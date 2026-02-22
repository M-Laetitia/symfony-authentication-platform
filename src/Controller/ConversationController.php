<?php 

namespace App\Controller;

// Vendors
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// App
use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\ServiceProposal;

use App\Enum\MessageType;
use App\Enum\ServiceProposalType;

use App\Form\MessageFormType;
use App\Form\ReportMessageFormType;
use App\Form\ServiceProposalActionFormType;
use App\Form\ServiceProposalFormType;

use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use App\Repository\PhotographRepository;
use App\Repository\TaxRepository;

use App\Service\MailerService;

class ConversationController extends AbstractController
{
    #[Route('/chat', name: 'chat')]
    public function index(ConversationRepository $conversationRepo, MessageRepository $messageRepo): Response
    {
        $user = $this->getUser();
        // check if the user is connected
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $conversations = $conversationRepo->findByUser($user);
        $otherParticipants = [];
        $conversationData = [];
        foreach ($conversations as $conv) {
            $other = $conversationRepo->findOtherParticipant($conv, $user);
            $otherParticipants[$conv->getId()] = $other ? $other->getUsername() : 'Aucun';
            $lastMessage = $messageRepo->findLastMessageForConversation($conv);

            $conversationData[] = [
                'id' => $conv->getId(),
                'otherParticipant' => $other ? $other->getUsername() : 'Aucun',
                'lastMessage' => $lastMessage ? $lastMessage->getContent() : 'Aucun message',
                'lastMessageDate' => $lastMessage ? $lastMessage->getCreationDate() : null,
            ];
        }

        
        return $this->render('chat/index.html.twig', [
            'user' => $this->getUser(),
            'conversations' => $conversations,
            'otherParticipants' => $otherParticipants, 
            'conversationData' => $conversationData,
        ]);
    }

    #[Route('/chat/conversation/{id}', name: 'chat_conversation_show')]
    public function show(
        Conversation $conversation,
        Message $message, 
        ConversationRepository $conversationRepo,
        MessageRepository $messageRepo,
        EntityManagerInterface $em,
        HubInterface $hub, 
        Request $request, 
    ): Response {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!$conversationRepo->isUserParticipant($conversation, $user)) {
            throw $this->createAccessDeniedException();
        }

        // $messages = $messageRepo->findByConversation($conversation);
        $messages = $messageRepo->findByConversationWithProposals($conversation);
        $otherParticipant = $conversationRepo->findOtherParticipant($conversation, $user);
        
        // display accept/refuse form
        $proposalActionForms = [];
        foreach ($messages as $msg) {
            if ($msg->getServiceProposal()) {
                $proposal = $msg->getServiceProposal();
                $proposalActionForms[$proposal->getId()] = $this->createForm(
                    ServiceProposalActionFormType::class,
                    null,
                    [
                        'action' => $this->generateUrl('proposal_action', [
                            'id' => $proposal->getId()
                        ]),
                        'method' => 'POST'
                    ]
                )->createView();
            }
        }

        // report message 
        $reportForm = $this->createForm(ReportMessageFormType::class);

        // add a message
        $message = new Message();
        $form = $this->createForm(MessageFormType::class, $message);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
    
            $message->setSender($user);
            $message->setConversation($conversation);
            $message->setStatus(MessageType::UNREAD);
            $message->setCreationDate(new \DateTimeImmutable());
            $em->persist($message);
            $em->flush();
    
            //  Mercure - topic = /conversation/{id} > chaque conversation a son propre topic
            $update = new Update(
                '/conversation/'.$conversation->getId(),
                json_encode([
                    'author' => $user->getUserIdentifier(),
                    'content' => $message->getContent(),
                    'date' => $message->getCreationDate()->format('H:i'),
                ])
            );
            // envoie le message à tous les clients abonnés à ce topic, en temps réel.
            $hub->publish($update); 

            // Réponse AJAX : $request->isXmlHttpRequest() > sinon redirect
            // Si la requête vient d’un AJAX fetch : pas besoin de recharger la page > renvoie un 204 No Content.
            if ($request->isXmlHttpRequest()) {
                return new Response(null, 204);
            }
    
            return $this->redirectToRoute('chat_conversation_show', [
                'id' => $conversation->getId()
            ]);
        }

        return $this->render('chat/show.html.twig', [
            'conversation' => $conversation,
            'messages' => $messages,
            'otherParticipant' => $otherParticipant,
            'form'=> $form->createView(),
            'reportForm' => $reportForm->createView(),
            'proposalActionForms' => $proposalActionForms,
        ]);
    }

    #[Route('/proposal/{id}/action', name: 'proposal_action', methods: ['POST'])]
    public function proposalAction(
        ServiceProposal $proposal,
        Request $request,
        EntityManagerInterface $em
    ): Response
    {
        $form = $this->createForm(ServiceProposalActionFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ClickableInterface $acceptButton */
            $acceptButton = $form->get('accept');
            /** @var ClickableInterface $refuseButton */
            $refuseButton = $form->get('refuse');
        
            if ($acceptButton->isClicked()) {
                $proposal->setStatus(ServiceProposalType::ACCEPTED);
            } elseif ($refuseButton->isClicked()) {
                $proposal->setStatus(ServiceProposalType::REJECTED);
            }
            $em->flush();
        }

        return $this->redirectToRoute('chat_conversation_show', [
            'id' => $proposal->getConversation()->getId(),
        ]);
    }

    #[Route('/chat/message/report', name: 'chat_message_report', methods: ['POST'])]
    #[IsGranted('ROLE_PHOTOGRAPH')]
    public function report(
        Request $request,
        EntityManagerInterface $em,
        MailerService $mailerService,
        MessageRepository $messageRepo
    ): Response {
        $user = $this->getUser();
        $form = $this->createForm(ReportMessageFormType::class);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $reason = $data['reason'];
    
            $messageId = $request->request->get('messageId');
            $message = $messageRepo->find($messageId);
    
            if (!$message) {
                throw $this->createNotFoundException('Message non trouvé.');
            }
    
            if ($message->getSender() === $user) {
                throw $this->createAccessDeniedException('Impossible de signaler son propre message.');
            }
    
            $message->setIsReported(true);
            $message->setReportReason($reason);
            $message->getConversation()->setIsFrozen(true);
    
            $em->flush();

            // Envoi des mails
            $mailerService->sendMessageReportedEmail($message, $user, $reason);
            $mailerService->sendAdminMessageReportNotification($message, $user, $reason);

            $this->addFlash('success', 'The message has been successfully reported. Emails have been sent to the user and the admin.');
    
            return $this->redirectToRoute('chat_conversation_show', [
                'id' => $message->getConversation()->getId(),
            ]);
        }
    
        // Si le formulaire pas valide, retourne une erreur
        return new Response('Formulaire invalide', 400);
    }


    #[Route('/conversation/{id}/proposal/new', name: 'proposal_new')]
    #[IsGranted('ROLE_PHOTOGRAPH')]
    public function createProposal(
        Conversation $conversation,
        ConversationRepository $conversationRepo,
        PhotographRepository $photographRepo ,
        Request $request, 
        EntityManagerInterface $em,
        TaxRepository $taxRepository,
    ): Response {

        $this->denyAccessUnlessGranted('ROLE_PHOTOGRAPH');
    
        $proposal = new ServiceProposal();
        $proposal->setConversation($conversation);

        $activeTaxes = $taxRepository->findBy(['active' => 1]);

        $form = $this->createForm(ServiceProposalFormType::class, $proposal, [
            'active_taxes' => $activeTaxes, 
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $photographUser = $this->getUser();
            $photograph = $photographRepo->findOneBy(['user' => $photographUser]);
            $client = $conversationRepo->findOtherParticipant($conversation, $photographUser);

            $proposal->setPhotograph($photograph);
            $proposal->setClient($client);
            $proposal->setCreatedAt(new \DateTimeImmutable());
            $proposal->setStatus(ServiceProposalType::PENDING);

            // Création du message lié
            $message = new Message();
            $message->setConversation($conversation);
            $message->setSender($photographUser);
            $message->setServiceProposal($proposal);
            $message->setStatus(MessageType::UNREAD);
            $message->setCreationDate(new \DateTimeImmutable());
            $em->persist($proposal);
            $em->persist($message);
            $em->flush();

            $this->addFlash('success', 'Proposal sent successfully.');

            return $this->redirectToRoute('chat_conversation_show', [
                'id' => $conversation->getId(),
            ]);
        }

        return $this->render('proposal/new.html.twig', [
            'form' => $form->createView(),
            'conversation' => $conversation,
        ]);
    }


    #[Route('/proposal/{id}/accept', name: 'proposal_accept', methods: ['POST'])]
    public function accept(
        ServiceProposal $proposal,
        Request $request,
        EntityManagerInterface $em
    ): Response {

        $form = $this->createForm(ServiceProposalActionFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $proposal->setStatus(ServiceProposalType::ACCEPTED);
            $em->flush();

            $this->addFlash('success', 'Proposal accepted successfully.');

            return $this->redirectToRoute('chat_conversation_show', [
                'id' => $proposal->getConversation()->getId(),
            ]);
        }

        return $this->redirectToRoute('chat_conversation_show', [
            'id' => $proposal->getConversation()->getId(),
        ]);

        // redirection vers la page de paiement
        // return $this->redirectToRoute('proposal_payment', ['id' => $proposal->getId()]);
    }
} 

