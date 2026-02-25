<?php 

namespace App\Controller;

// Vendors
use App\Entity\Message;
use App\Enum\MessageType;
use App\Entity\Conversation;
use App\Entity\Photographer;
use Psr\Log\LoggerInterface;
use App\Form\MessageFormType;
use App\Enum\ConversationType;
use App\Service\MailerService;

// App
use App\Entity\ServiceProposal;
use App\Enum\ServiceProposalType;
use App\Repository\TaxRepository;
use App\Service\MessagingService;

use App\Form\ReportMessageFormType;
use App\Form\ServiceProposalFormType;
use App\Repository\MessageRepository;

use Symfony\Component\Mercure\Update;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ConversationRepository;
use App\Repository\PhotographerRepository;

use App\Form\ServiceProposalActionFormType;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Authorization;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mercure\Jwt\TokenFactoryInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ConversationController extends AbstractController
{
    public function __construct(
        private LoggerInterface $messagesLogger,
        private RateLimiterFactoryInterface $messageSendingLimiter
    ) {}

    #[Route('/chat', name: 'chat')]
    public function index(
        ConversationRepository $conversationRepo,
        MessageRepository $messageRepo
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $userId = $user->getId();


        $conversations = $conversationRepo->findByAuthenticatedUser($user);
        $conversationIds = array_map(
            fn($conv) => $conv->getId(),
            $conversations
        );

        $unreadByConversationRaw = $messageRepo
            ->countUnreadByConversations($conversationIds, $userId);

        $unreadByConversation = [];
        foreach ($unreadByConversationRaw as $row) {
            $unreadByConversation[$row['conversationId']] = $row['unreadCount'];
        }

        $lastMessages = $messageRepo->findLastMessagesForConversations($conversations);

        $lastMessagesByConversation = [];
        foreach ($lastMessages as $msg) {
            $lastMessagesByConversation[$msg->getConversation()->getId()] = $msg;
        }

        foreach ($conversations as $conv) {

            if ($conv->getClient() === $user) {
                $otherUser = $conv->getPhotographer()->getUser();
            } else {
                $otherUser = $conv->getClient();
            }
        
            $lastMessage = $lastMessagesByConversation[$conv->getId()] ?? null;
        
            $conversationData[] = [
                'id' => $conv->getId(),
                'otherParticipant' => $otherUser->getUsername(),
                'lastMessage' => $lastMessage?->getContent() ?? 'no message',
                'lastMessageDate' => $lastMessage?->getCreationDate(),
                'authorLastMessage' => $lastMessage?->getSender()?->getUsername(),
                'unreadCount' => $unreadByConversation[$conv->getId()] ?? 0,
            ];
        }

        $unreadMessages = $messageRepo->countUnreadForUser($userId);

        return $this->render('chat/index.html.twig', [
            'conversations' => $conversations,
            'conversationData' => $conversationData,
            'unreadMessages' => $unreadMessages,
        ]);
    }

    #[Route('/chat/conversation/{id}', name: 'chat_conversation_show', methods: ['GET'])]
    public function show(
        Conversation $conversation,
        MessageRepository $messageRepo,
        TokenFactoryInterface $defaultTokenFactory,
        Request $request,
        MessagingService $messagingService,
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
    
        $isClient = $conversation->getClient()->getId() === $user->getId();
        $isPhotographer = $conversation->getPhotographer()
            && $conversation->getPhotographer()->getUser()->getId() === $user->getId();
    
        if (!$isClient && !$isPhotographer) {
            throw $this->createAccessDeniedException();
        }

        $messagingService->openConversation($conversation, $user);
    
        if ($isClient) {
            $otherParticipant = $conversation->getPhotographer()->getUser();
        } else {
            $otherParticipant = $conversation->getClient();
        }
    
        $messages = $messageRepo->findByConversationWithProposals($conversation);
    
        $proposalActionForms = [];
        foreach ($messages as $msg) {
            if ($msg->getServiceProposal()) {
                $proposal = $msg->getServiceProposal();
                $proposalActionForms[$proposal->getId()] = $this->createForm(
                    ServiceProposalActionFormType::class,
                    null,
                    [
                        'action' => $this->generateUrl('proposal_action', ['id' => $proposal->getId()]),
                        'method' => 'POST'
                    ]
                )->createView();
            }
        }
    
        $reportForm = $this->createForm(ReportMessageFormType::class);
        $form = $this->createForm(MessageFormType::class, new Message(), [
            'action' => $this->generateUrl('chat_message_send', ['id' => $conversation->getId()]),
        ]);

        $token = $defaultTokenFactory->create([
            '/conversation/' . $conversation->getId()
        ]);
    
        $response = $this->render('chat/show.html.twig', [
            'conversation' => $conversation,
            'messages' => $messages,
            'otherParticipant' => $otherParticipant,
            'form' => $form->createView(),
            'reportForm' => $reportForm->createView(),
            'proposalActionForms' => $proposalActionForms,
        ]);

        $response->headers->setCookie(
            new \Symfony\Component\HttpFoundation\Cookie(
                'mercureAuthorization',  // nom du cookie — Mercure cherche spécifiquement ce nom
                $token,                  // valeur = le JWT généré par Symfony
                0,                       // expiration = 0 signifie "cookie de session" (supprimé à la fermeture du navigateur)
                '/',                     // path = accessible sur toute l'application (pas seulement /.well-known/mercure)
                null,                    // domain = null = domaine actuel (localhost)
                false,                   // secure = false en dev (true en prod = cookie envoyé uniquement en HTTPS)
                true,                    // httpOnly = true = le cookie n'est PAS accessible via JavaScript (document.cookie) → protège contre le vol de token via XSS
                false,                   // raw = false = le nom/valeur du cookie sont encodés normalement
                'strict'                 // sameSite = strict = le cookie n'est envoyé que si la requête vient du même site → protège contre les attaques CSRF
            )
        );
        
        // dump($response->headers->getCookies()); die;

        return $response;
    }
    
    #[Route('/chat/conversation/{id}/message', name: 'chat_message_send', methods: ['POST'])]
    public function sendMessage(
        Conversation $conversation,
        EntityManagerInterface $em,
        HubInterface $hub,
        Request $request,
        HtmlSanitizerInterface $htmlSanitizer,
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
    
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $isClient = $conversation->getClient()->getId() === $user->getId();
        $isPhotographer = $conversation->getPhotographer()
            && $conversation->getPhotographer()->getUser()->getId() === $user->getId();
    
        if (!$isClient && !$isPhotographer) {
            throw $this->createAccessDeniedException();
        }
    
        $message = new Message();
        $form = $this->createForm(MessageFormType::class, $message);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // $message->setContent($htmlSanitizer->sanitize($message->getContent()));
            $rawContent = $message->getContent();
            $sanitized = $htmlSanitizer->sanitize($rawContent);

            // Si le contenu a changé après sanitization → tentative suspecte
            if ($rawContent !== $sanitized) {
                $this->messagesLogger->warning('[Chat] XSS attempt detected in message content', [
                    'user'            => $user->getUserIdentifier(),
                    'conversation_id' => $conversation->getId(),
                    'ip'              => $request->getClientIp(),
                    'route'           => $request->attributes->get('_route'),
                    'raw_content'     => $rawContent,
                ]);
            }
            // Message vide si tout le contenu était malveillant
            if (empty(trim($sanitized))) {
                return new JsonResponse(['status' => 'error', 'message' => 'Invalid content'], 400);
            }

            $limit = $this->messageSendingLimiter->create($request->getClientIp())->consume(1);
    
            if (!$limit->isAccepted()) {
                $this->messagesLogger->warning('[Chat] Rate limit reached', [
                    'user' => $user->getUserIdentifier(),
                    'ip'   => $request->getClientIp(),
                    'conversation_id' => $conversation->getId(),
                    'route'           => $request->attributes->get('_route')
                ]);
                return new JsonResponse(['status' => 'error', 'message' => 'Too many messages, please slow down'], 429);
            }

            $message->setContent($sanitized);
            $message->setSender($user);
            $message->setConversation($conversation);
            $message->setStatus(MessageType::UNREAD);
            $message->setCreationDate(new \DateTimeImmutable());
            $em->persist($message);
            $em->flush();
    
            $update = new Update(
                '/conversation/' . $conversation->getId(),
                json_encode([
                    'author' => $user->getUserIdentifier(),
                    'content' => $message->getContent(),
                    'date' => $message->getCreationDate()->format('H:i'),
                ])
            );
            $hub->publish($update);
    
            return new JsonResponse(['status' => 'ok']);
        }
    
        return new JsonResponse(['status' => 'error', 'errors' => (string) $form->getErrors(true)], 400);
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
    #[IsGranted('ROLE_PHOTOGRAPHER')]
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
    #[IsGranted('ROLE_PHOTOGRAPHER')]
    public function createProposal(
        Conversation $conversation,
        ConversationRepository $conversationRepo,
        PhotographerRepository $photographerRepo ,
        Request $request, 
        EntityManagerInterface $em,
        TaxRepository $taxRepository,
    ): Response {

        $this->denyAccessUnlessGranted('ROLE_PHOTOGRAPHER');
    
        $proposal = new ServiceProposal();
        $proposal->setConversation($conversation);

        $activeTaxes = $taxRepository->findBy(['active' => 1]);

        $form = $this->createForm(ServiceProposalFormType::class, $proposal, [
            'active_taxes' => $activeTaxes, 
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $photographerUser = $this->getUser();
            $photographer = $photographerRepo->findOneBy(['user' => $photographerUser]);
            $client = $conversationRepo->findOtherParticipant($conversation, $photographerUser);

            $proposal->setPhotographer($photographer);
            $proposal->setClient($client);
            $proposal->setCreatedAt(new \DateTimeImmutable());
            $proposal->setStatus(ServiceProposalType::PENDING);

            // Création du message lié
            $message = new Message();
            $message->setConversation($conversation);
            $message->setSender($photographerUser);
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

    #[Route('/chat/start/{id}', name: 'chat_start')]
    public function startChat(
        Photographer $photographer, 
        EntityManagerInterface $em,
        ConversationRepository $conversationRepository
    ): Response {
        $client = $this->getUser();
        if (!$client) {
            throw $this->createAccessDeniedException();
        }

        $conversation = $conversationRepository->findOneBy([
            'client' => $client,
            'photographer' => $photographer
        ]);

        if (!$conversation) {
            $conversation = new Conversation();
            $conversation->setClient($client);
            $conversation->setPhotographer($photographer);
            $conversation->setStatus([ConversationType::ACTIVE]);
            $conversation->setIsFrozen(false);
            $conversation->setCreationDate(new \DateTimeImmutable());

            $em->persist($conversation);
            $em->flush();
        }


        return $this->redirectToRoute('chat_conversation_show', [
            'id' => $conversation->getId()
        ]);
    }
} 

