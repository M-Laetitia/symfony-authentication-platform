<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\User;
use App\Entity\Conversation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

//    /**
//     * @return Message[] Returns an array of Message objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Message
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

        public function findLastMessagesForConversations(array $conversations): array
        {
            return $this->createQueryBuilder('m')
                ->addSelect('sender')
                ->join('m.sender', 'sender') 
                ->where('m.conversation IN (:convs)')
                ->andWhere('m.creation_date = (
                    SELECT MAX(m2.creation_date)
                    FROM App\Entity\Message m2
                    WHERE m2.conversation = m.conversation
                )')
                ->setParameter('convs', $conversations)
                ->getQuery()
                ->getResult();
        }

    public function findByConversationWithProposals(Conversation $conversation): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.serviceProposal', 'sp')
            ->addSelect('sp') 
            ->where('m.conversation = :conv')
            ->setParameter('conv', $conversation)
            ->orderBy('m.creation_date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findUnreadByConversations(array $conversationIds)
    {
        if (empty($conversationIds)) {
            return [];
        }
    
        return $this->createQueryBuilder('m')
            ->where('m.conversation IN (:ids)')
            ->andWhere('m.status = :status')
            ->setParameter('ids', $conversationIds)
            ->setParameter('status', 'unread')
            ->getQuery()
            ->getResult();
    }

    
    public function countUnreadForUser(int $userId): int
    {
        return (int) $this->createQueryBuilder('m')
            ->join('m.conversation', 'c')
            ->leftJoin('c.client', 'client')
            ->leftJoin('c.photographer', 'photographer')
            ->where('(client.id = :userId OR photographer.id = :userId)') 
            ->andWhere('m.status = :status')                             
            ->andWhere('m.sender != :userId')                           
            ->setParameter('userId', $userId)
            ->setParameter('status', 'unread')
            ->select('COUNT(m.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countUnreadByConversations(array $conversationIds, int $userId): array
        {
            if (empty($conversationIds)) {
                return [];
            }

            return $this->createQueryBuilder('m')
                ->select('IDENTITY(m.conversation) as conversationId, COUNT(m.id) as unreadCount')
                ->where('m.conversation IN (:ids)')
                ->andWhere('m.status = :status')
                ->andWhere('m.sender != :userId')
                ->setParameter('ids', $conversationIds)
                ->setParameter('status', 'unread')
                ->setParameter('userId', $userId)
                ->groupBy('m.conversation')
                ->getQuery()
                ->getResult();
        }

        public function markAsRead(Conversation $conversation, User $user): int
        {
            return $this->createQueryBuilder('m')
                ->update()
                ->set('m.status', ':read')
                ->where('m.conversation = :conversation')
                ->andWhere('m.sender != :user') 
                ->andWhere('m.status = :unread')
                ->setParameter('read', 'read')
                ->setParameter('unread', 'unread')
                ->setParameter('conversation', $conversation)
                ->setParameter('user', $user)
                ->getQuery()
                ->execute(); 
        }

}
