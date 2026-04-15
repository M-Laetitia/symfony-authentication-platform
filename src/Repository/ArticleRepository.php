<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Enum\MediaType;
use Doctrine\ORM\Query;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }


    public function findPublishedArticlesWithCover(?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('partial a.{id, title, slug, createdAt, status, excerpt}', 'm')
            ->distinct()
            ->leftJoin('a.medias', 'm', 'WITH', 'm.typeImage = :typeImage')
            ->where('a.status = :status')
            ->setParameter('status', 'published')
            ->setParameter('typeImage', MediaType::ARTICLE_COVER)
            ->orderBy('a.createdAt', 'DESC');

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
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

    /**
     * Finds published articles matching the search criteria in title or tags
     * Supports multi-word search with OR logic between words
     * 
     * @param string $search The search query (can contain multiple space-separated words)
     * @return Query Returns a Doctrine Query object (not executed) for pagination compatibility
     */
    public function findPublishedArticlesBySearch(string $search)
    {
        // Initialize QueryBuilder with Article entity alias 'a'
        $qb = $this->createQueryBuilder('a')
            // LEFT JOIN with tags to search in tag names (preserves articles without tags)
            ->leftJoin('a.tags', 't')
            ->where('a.status = :status')
            ->setParameter('status', 'published');

        // Split search string into individual words using whitespace as delimiter
        // preg_split handles multiple consecutive spaces better than explode
        $words = preg_split('/\s+/', trim($search));

        // Create an OR expression container for multiple search conditions
        $orX = $qb->expr()->orX();

        // Build dynamic WHERE conditions for each word
        foreach ($words as $key => $word) {
            // Add LIKE condition for article title
            $orX->add("a.title LIKE :word$key");
            // Add LIKE condition for tag name
            $orX->add("t.name LIKE :word$key");

            // Bind parameter with wildcard for partial matching
            // Using unique parameter names (:word0, :word1, etc.) to avoid conflicts
            $qb->setParameter("word$key", '%' . $word . '%');
        }

        // Apply the OR conditions to the query
        // Results will match if ANY word is found in title OR tags
        $qb->andWhere($orX);

        return $qb
            // Sort by most recent articles first
            ->orderBy('a.createdAt', 'DESC')
            // Remove duplicate articles (important when joining with tags collection)
            ->distinct()
            // Return Query object (not executed) to allow pagination
            ->getQuery();
    }

   
    /**
     * Find all published articles for a specific category
     * 
     * @param Category $category The category to filter by
     * @return Query Doctrine Query object for pagination
     */
    public function findPublishedArticlesByCategory(Category $category): Query
    {
        return $this->createQueryBuilder('a')
            ->where('a.status = :status')
            ->andWhere('a.category = :category')
            ->setParameter('status', 'published')
            ->setParameter('category', $category)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery();
    }

    public function findAllForAdmin(): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.comments', 'c')
            ->addSelect('c')
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAllForAdminFiltered(string $sortBy = 'date_desc', string $status = '', string $featured = '', string $categoryId = ''): array
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.comments', 'c')
            ->addSelect('c')
            ->leftJoin('a.category', 'cat')
            ->addSelect('cat');

        // Status filter
        if ($status !== '') {
            $qb->andWhere('a.status = :status')
               ->setParameter('status', $status);
        }

        // Category filter
        if ($categoryId !== '') {
            $qb->andWhere('a.category = :categoryId')
               ->setParameter('categoryId', (int)$categoryId);
        }

        // Featured filter
        if ($featured === 'yes') {
            $qb->andWhere('a.isFeatured = true');
        } elseif ($featured === 'no') {
            $qb->andWhere('a.isFeatured = false OR a.isFeatured IS NULL');
        }

        // Sorting
        switch ($sortBy) {
            case 'date_asc':
                $qb->orderBy('a.createdAt', 'ASC');
                break;
            case 'date_desc':
            default:
                $qb->orderBy('a.createdAt', 'DESC');
                break;
        }

        return $qb->getQuery()->getResult();
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
