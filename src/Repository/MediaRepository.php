<?php

namespace App\Repository;

use App\Entity\Media;
use App\Entity\Photographer;
use App\Enum\MediaType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Media>
 */
class MediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Media::class);
    }

    public function findPortfolioCoverByPhotographer(Photographer $photographer): ?Media
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.photographer = :photographer')
            ->andWhere('m.typeImage = :type')
            ->setParameter('photographer', $photographer)
            ->setParameter('type', MediaType::PORTFOLIO_COVER)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    // public function findArticleCoverByArticleId(int $articleId): ?Media
    // {
    //     /** @var array<string, mixed> $params */
    //     $params = [
    //         'articleId' => $articleId,
    //         'typeImage' => MediaType::ARTICLE_COVER,
    //     ];
    //     return $this->createQueryBuilder('m')
    //         ->innerJoin('m.Article', 'a')
    //         ->where('a.id = :articleId')
    //         ->andWhere('m.typeImage = :typeImage')
    //         ->setParameters($params)
    //         // ->setParameters(new \Doctrine\Common\Collections\ArrayCollection(
    //         //     [ 'articleId' => $articleId, 
    //         //     'typeImage' => MediaType::ARTICLE_COVER, ]
    //         //     ))
    //         ->setMaxResults(1)
    //         ->getQuery()
    //         ->getOneOrNullResult();
    // }

//    /**
//     * @return Media[] Returns an array of Media objects
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

//    public function findOneBySomeField($value): ?Media
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
