<?php 

namespace App\Controller;

use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use App\Entity\Conversation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
        ConversationRepository $conversationRepo,
        MessageRepository $messageRepo
    ): Response
    {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!$conversationRepo->isUserParticipant($conversation, $user)) {
            throw $this->createAccessDeniedException();
        }

        $messages = $messageRepo->findByConversation($conversation);
        $otherParticipant = $conversationRepo->findOtherParticipant($conversation, $user);

        return $this->render('chat/show.html.twig', [
            'conversation' => $conversation,
            'messages' => $messages,
            'otherParticipant' => $otherParticipant,
        ]);
    }
}