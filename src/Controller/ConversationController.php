<?php 

namespace App\Controller;

use App\Entity\Message;
use App\Enum\MessageType;
use App\Entity\Conversation;
use App\Form\MessageFormType;
use App\Form\ReportMessageFormType;
use App\Repository\MessageRepository;
use Symfony\Component\Mercure\Update;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ConversationRepository;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    ): Response
    {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!$conversationRepo->isUserParticipant($conversation, $user)) {
            throw $this->createAccessDeniedException();
        }

        $messages = $messageRepo->findByConversation($conversation);
        $otherParticipant = $conversationRepo->findOtherParticipant($conversation, $user);

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
            $mailerService->sendMessageReportedEmail(
                $message,
                $user,
                $reason
            );
            
            $mailerService->sendAdminMessageReportNotification(
                $message,
                $user,
                $reason
            );
            
    
            return $this->redirectToRoute('chat_conversation_show', [
                'id' => $message->getConversation()->getId(),
            ]);
        }
    
        // Si le formulaire n'est pas valide, retourne une erreur
        return new Response('Formulaire invalide', 400);
    }
} 