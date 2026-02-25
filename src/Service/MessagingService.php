<?php

namespace App\Service;

use App\Entity\Conversation;
use App\Entity\User;
use App\Repository\MessageRepository;

class MessagingService
{
    public function __construct(
        private MessageRepository $messageRepository
    ) {}

    public function openConversation(Conversation $conversation, User $user): void
    {
        $this->messageRepository->markAsRead($conversation, $user);
    }
}