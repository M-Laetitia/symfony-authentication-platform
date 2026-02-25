<?php

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Conversation>
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    //    /**
    //     * @return Conversation[] Returns an array of Conversation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Conversation
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findByUser($user)
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.participants', 'p') 
            ->addSelect('p')
            ->where('p.id = :userId')
            ->setParameter('userId', $user->getId())
            ->orderBy('c.creation_date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOtherParticipant(Conversation $conversation, User $user): ?User
    {
        return $this->getEntityManager()->createQuery(
            'SELECT u
            FROM App\Entity\User u
            JOIN u.conversations c
            WHERE c.id = :convId
            AND u.id != :userId'
        )
        ->setParameter('convId', $conversation->getId())
        ->setParameter('userId', $user->getId())
        ->setMaxResults(1)
        ->getOneOrNullResult();
    }

    public function isUserParticipant(Conversation $conversation, User $user): bool
    {
        return (bool) $this->createQueryBuilder('c')
            ->innerJoin('c.participants', 'p')
            ->where('c.id = :convId')
            ->andWhere('p.id = :userId')
            ->setParameter('convId', $conversation->getId())
            ->setParameter('userId', $user->getId())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByAuthenticatedUser(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.photographer', 'p')
            ->leftJoin('p.user', 'pc')
            ->where('c.client = :user')
            ->orWhere('pc = :user')
            ->setParameter('user', $user)
            ->orderBy('c.creation_date', 'DESC')
            ->getQuery()
            ->getResult();
    }
    public function findConversationsForUser(int $userId)
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.client', 'client')
            ->leftJoin('c.photographer', 'photographer')
            ->where('client.id = :userId OR photographer.id = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

}
