<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Enum\MediaType;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }



    public function findPublishedArticlesWithCover(): array
    {
        return $this->createQueryBuilder('a')
            ->select('partial a.{id, title, slug, createdAt, status, excerpt}', 'm')
            ->distinct()
            ->leftJoin('a.medias', 'm', 'WITH', 'm.typeImage = :typeImage')
            ->where('a.status = :status')
            ->setParameter('status', 'published')
            ->setParameter('typeImage', MediaType::ARTICLE_COVER)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findTopArticles(int $limit = 5): array
    {
        return $this->createQueryBuilder('a')
            ->select('a')
            ->where('a.isFeatured = :featured')
            ->andWhere('a.status = :status')
            ->setParameter('featured', true)
            ->setParameter('status', 'published')
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult(); 
    }

    public function findPreviousArticle(\DateTimeInterface $date): ?Article
    {
        return $this->createQueryBuilder('a')
            ->where('a.createdAt < :date')
            ->setParameter('date', $date)
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findNextArticle(\DateTimeInterface $date): ?Article
    {
        return $this->createQueryBuilder('a')
            ->where('a.createdAt > :date')
            ->setParameter('date', $date)
            ->orderBy('a.createdAt', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    public function findRelatedArticles(Article $article): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.tags', 't')
            ->addSelect('COUNT(t.id) AS HIDDEN tagMatch')
            ->andWhere('a.category = :cat OR t IN (:tags)')
            ->setParameter('cat', $article->getCategory())
            ->setParameter('tags', $article->getTags())
            ->andWhere('a.id != :id')
            ->setParameter('id', $article->getId())
            ->groupBy('a.id')
            ->orderBy('tagMatch', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    }
    
    //    /**
    //     * @return Article[] Returns an array of Article objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Article
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
