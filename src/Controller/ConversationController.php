<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\Photographer;
use App\Entity\ServiceProposal;
use App\Enum\ConversationType;
use App\Enum\MessageType;
use App\Enum\ServiceProposalType;
use App\Form\MessageFormType;
use App\Form\ReportMessageFormType;
use App\Form\ServiceProposalFormType;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use App\Repository\PhotographerRepository;
use App\Repository\TaxRepository;
use App\Service\MailerService;
use App\Service\MessagingService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Jwt\TokenFactoryInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;



class ConversationController extends AbstractController
{
    public function __construct(
        private LoggerInterface $messagesLogger,
        private RateLimiterFactoryInterface $messageSendingLimiter,
    ) {
    }
    
    // ^ NEW CONVERSATION (START CHAT)
    #[Route('/chat/start/{id}', name: 'chat_start')]
    public function startChat(
        Photographer $photographer,
        EntityManagerInterface $em,
        ConversationRepository $conversationRepository,
    ): Response {
        $client = $this->getUser();
        if (!$client) {
            throw $this->createAccessDeniedException();
        }

        $conversation = $conversationRepository->findOneBy([
            'client' => $client,
            'photographer' => $photographer,
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
            'id' => $conversation->getId(),
        ]);
    }

    // ^ CONVERSATION LIST 
    #[Route('/chat', name: 'chat')]
    public function index(
        ConversationRepository $conversationRepo,
        MessageRepository $messageRepo,
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $userId = $user->getId();

        $conversations = $conversationRepo->findByAuthenticatedUser($user);
        $conversationIds = array_map(
            fn ($conv) => $conv->getId(),
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

    // ^ SHOW CONVERSATION + MESSAGES
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


        $reportForm = $this->createForm(ReportMessageFormType::class);
        $form = $this->createForm(MessageFormType::class, new Message(), [
            'action' => $this->generateUrl('chat_message_send', ['id' => $conversation->getId()]),
        ]);

        $token = $defaultTokenFactory->create([
            '/conversation/'.$conversation->getId(),
        ]);

        $response = $this->render('chat/show.html.twig', [
            'conversation' => $conversation,
            'messages' => $messages,
            'otherParticipant' => $otherParticipant,
            'form' => $form->createView(),
            'reportForm' => $reportForm->createView(),
        ]);

        $response->headers->setCookie(
            new \Symfony\Component\HttpFoundation\Cookie(
                'mercureAuthorization',  // Cookie name — Mercure specifically looks for this name to authenticate the user for real-time updates
                $token,                  //  value = JWT generated by the Mercure TokenFactory, containing the user's permissions for subscribing to Mercure updates
                0,                       // expiration = 0 = meaning the cookie will expire at the end of the session (when the browser is closed)
                '/',                     // path = '/' = the cookie is sent for all paths on the domain, ensuring Mercure updates are received regardless of the current page (important if Mercure endpoint is on a different path)
                null,                    // domain = null = the cookie is valid for the current domain (e.g., localhost in dev, or your actual domain in prod)
                false,                   // secure = false = the cookie is sent over both HTTP and HTTPS (in development, since we often use HTTP; in production, this should be set to true to ensure the cookie is only sent over secure HTTPS connections) --->
                true,                    // httpOnly = true = the cookie is NOT accessible via JavaScript (document.cookie) → protects against token theft via XSS
                false,                   // raw = false = the cookie name and value are encoded normally (not URL-encoded) --> Mercure expects the token to be sent as-is, so we set raw to false to avoid double encoding
                'strict'                 // sameSite = 'strict' = the cookie is not sent with cross-site requests → protects against CSRF attacks by ensuring the cookie is only sent when the request originates from the same site (important for security, especially since this cookie contains an authentication token) --->
            )
        );

        return $response;
    }

    // ^ SEND MESSAGE
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
            $rawContent = $message->getContent();
            $sanitized = $htmlSanitizer->sanitize($rawContent);

            // If the content was changed after sanitization → suspicious attempt
            if ($rawContent !== $sanitized) {
                $this->messagesLogger->warning('[Chat] XSS attempt detected in message content', [
                    'user' => $user->getUserIdentifier(),
                    'conversation_id' => $conversation->getId(),
                    'ip' => $request->getClientIp(),
                    'route' => $request->attributes->get('_route'),
                    'raw_content' => $rawContent,
                ]);
            }
            // If the sanitized content is empty but the original content was not → likely an attempt to send malicious content
            if (empty(trim($sanitized))) {
                return new JsonResponse(['status' => 'error', 'message' => 'Invalid content'], 400);
            }

            $limit = $this->messageSendingLimiter->create($request->getClientIp())->consume(1);

            if (!$limit->isAccepted()) {
                $this->messagesLogger->warning('[Chat] Rate limit reached', [
                    'user' => $user->getUserIdentifier(),
                    'ip' => $request->getClientIp(),
                    'conversation_id' => $conversation->getId(),
                    'route' => $request->attributes->get('_route'),
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
                '/conversation/'.$conversation->getId(),
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

    // ^ REPORT MESSAGE
    #[Route('/chat/message/report', name: 'chat_message_report', methods: ['POST'])]
    #[IsGranted('ROLE_PHOTOGRAPHER')]
    public function report(
        Request $request,
        EntityManagerInterface $em,
        MailerService $mailerService,
        MessageRepository $messageRepo,
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
                throw $this->createNotFoundException('Message none found.');
            }

            if ($message->getSender() === $user) {
                throw $this->createAccessDeniedException('You cannot report your own message.');
            }

            $message->setIsReported(true);
            $message->setReportReason($reason);
            $message->getConversation()->setIsFrozen(true);

            $em->flush();

            // Send emails to both the user and the admin
            $mailerService->sendMessageReportedEmail($message, $user, $reason);
            $mailerService->sendAdminMessageReportNotification($message, $user, $reason);

            $this->addFlash('success', 'The message has been successfully reported. Emails have been sent to the user and the admin.');

            return $this->redirectToRoute('chat_conversation_show', [
                'id' => $message->getConversation()->getId(),
            ]);
        }

        // If the form is not valid, redirect back with an error message
        return new Response('Invalid form', 400);
    }

    // ^ CREATE PROPOSAL
    #[Route('/conversation/{id}/proposal/new', name: 'proposal_new')]
    #[IsGranted('ROLE_PHOTOGRAPHER')]
    public function createProposal(
        Conversation $conversation,
        ConversationRepository $conversationRepo,
        PhotographerRepository $photographerRepo,
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

            // Create a new message in the conversation with the proposal details
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

    // ^ ACCEPT PROPOSAL
    #[Route('/chat/proposal/accept/{id}', name: 'proposal_accept', methods: ['POST'])]
    public function acceptProposal(
        ServiceProposal $proposal,
        Request $request,
    ): Response {
        // Ask for confirmation
        if ('request_confirmation' === $request->request->get('action')) {
            if (!$this->isCsrfTokenValid('accept_proposal_'.$proposal->getId(), $request->request->get('_token'))) {
                return new JsonResponse(['error' => 'An error occurred. Please try again.'], 400);
            }

            if (!$this->canAcceptProposal($proposal)) {
                throw $this->createAccessDeniedException('You cannot accept this proposal.');
            }

            return $this->render('chat/_confirmation_modal.html.twig', [
                'proposal' => $proposal,
            ]);
        }

        // Final confirmation from modal
        if ('confirm' === $request->request->get('action')) {
            if (!$this->isCsrfTokenValid('confirm_proposal_'.$proposal->getId(), $request->request->get('_confirm_token'))) {
                $this->addFlash('error', 'An error occurred. Please try again.');

                return $this->redirectToRoute('chat_conversation_show', [
                    'id' => $proposal->getConversation()->getId(),
                ]);
            }

            if (!$this->canAcceptProposal($proposal)) {
                throw $this->createAccessDeniedException('You cannot accept this proposal.');
            }

            return $this->redirectToRoute('create_order', ['id' => $proposal->getId()]);
        }

        // Fallback
        return $this->redirectToRoute('chat_conversation_show', [
            'id' => $proposal->getConversation()->getId(),
        ]);
    }

    // ^ REFUSE PROPOSAL
    #[Route('/chat/proposal/refuse/{id}', name: 'proposal_refuse', methods: ['POST'])]
    public function refuseProposal(
        ServiceProposal $proposal,
        Request $request,
        EntityManagerInterface $em,
    ): Response {
        // 1st POST => ask for confirmation
        if ('request_confirmation' === $request->request->get('action')) {
            if (!$this->isCsrfTokenValid('refuse_proposal_'.$proposal->getId(), $request->request->get('_token'))) {
                return new JsonResponse(['error' => 'An error occurred. Please try again.'], 400);
            }

            if (!$this->canAcceptProposal($proposal)) {
                throw $this->createAccessDeniedException('You cannot refuse this proposal.');
            }

            return $this->render('chat/_refuse_modal.html.twig', [
                'proposal' => $proposal,
            ]);
        }

        // 2nd POST => final refusal after confirmation
        if ('confirm' === $request->request->get('action')) {
            
            if (!$this->isCsrfTokenValid('confirm_refuse_'.$proposal->getId(), $request->request->get('_confirm_token'))) {
                $this->addFlash('error', 'An error occurred. Please try again.');

                return $this->redirectToRoute('chat_conversation_show', [
                    'id' => $proposal->getConversation()->getId(),
                ]);
            }

            if (!$this->canAcceptProposal($proposal)) {
                throw $this->createAccessDeniedException('You cannot refuse this proposal.');
            }

            $proposal->setStatus(ServiceProposalType::REJECTED);
            $em->flush();

            $this->addFlash('success', 'The proposal has been declined successfully.');

            // AJAX request, return JSON response
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'status' => 'success',
                    'redirect_url' => $this->generateUrl('chat_conversation_show', ['id' => $proposal->getConversation()->getId()]),
                ]);
            }

            // Fallback for non-AJAX request
            return $this->redirectToRoute('chat_conversation_show', [
                'id' => $proposal->getConversation()->getId(),
            ]);
        }

        // Fallback
        return $this->redirectToRoute('chat_conversation_show', [
            'id' => $proposal->getConversation()->getId(),
        ]);
    }

    // ^ HELPER METHOD TO CHECK IF PROPOSAL CAN BE ACCEPTED/REFUSED
    private function canAcceptProposal(ServiceProposal $proposal): bool
    {
        return $proposal->getConversation()->getClient() === $this->getUser()
            && ServiceProposalType::PENDING === $proposal->getStatus();
    }

}
