<?php

namespace App\Repository;

use App\Entity\Message;
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

    public function findLastMessageForConversation(Conversation $conversation): ?Message
    {
        return $this->createQueryBuilder('m')
            ->where('m.conversation = :conv')
            ->setParameter('conv', $conversation)
            ->orderBy('m.creation_date', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
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

}
