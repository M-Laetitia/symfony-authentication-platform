<?php 
namespace App\Controller;

error_reporting(E_ALL);
ini_set('display_errors', 1);

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Media;
use App\Service\MediaUploader;
use App\Form\ArticleFormType;
use App\Form\SearchArticleFormType;
use App\Form\CommentFormType;
use App\Repository\CommentRepository;
use App\Enum\MediaType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Service\CommentSecurityService;
use App\Service\SeoService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;



class ArticleController extends AbstractController
{
    #[Route('/blog', name: 'blog_index')]
    public function index(
        EntityManagerInterface $em, 
        ArticleRepository $articleRepo, 
        SeoService $seoService, 
        CategoryRepository $categoryRepo, 
        PaginatorInterface $paginator, 
        Request $request
        ): Response {

        $form = $this->createForm(SearchArticleFormType::class);
        $form->handleRequest($request);
        
        $search = '';
        if ($form->isSubmitted() && $form->isValid()) {
            $search = $form->get('search')->getData() ?? '';
        }

        $categorySlug = $request->query->get('category');
        $category = null;
            if ($categorySlug) {
                $category = $categoryRepo->findOneBy(['slug' => $categorySlug]);
        }
        
        if ($search) {
            $queryBuilder = $articleRepo->findPublishedArticlesBySearch($search);
        } elseif ($category) {
            $queryBuilder = $articleRepo->findPublishedArticlesByCategory($category);
        } else {
            $queryBuilder = $articleRepo->findPublishedArticlesWithCover();
        }

        // $queryBuilder = $search ? $articleRepo->findPublishedArticlesBySearch($search) : $articleRepo->findPublishedArticlesWithCover();
        $categories = $categoryRepo->findCategoriesWithArticleCount();
        $topArticles = $articleRepo->findTopArticles(5);


        $pagination = $paginator->paginate(
            $queryBuilder,                       
            $request->query->getInt('page', 1),    
            6                                      
        );


        return $this->render('blog/article/index.html.twig', [
            'pagination' => $pagination,
            'categories' => $categories,
            'search' => $search,
            'topArticles' => $topArticles,
            'searchForm' => $form->createView(),
            'selectedCategory' => $category,
            'meta_description' => $seoService->getMetaDescription('blog'),
            'meta_robots' => $seoService->getMetaRobots('blog'),
        ]);
    }

    // liste article pour partie dashboard pour admin
    #[Route('/admin/blog', name: 'admin_blog_index')]
    #[IsGranted('ROLE_PHOTOGRAPHER')]
    public function adminIndex(ArticleRepository $articleRepo): Response
    {
        $articles = $articleRepo->findAllForAdmin();
        

        $stats = [
            'total' => count($articles),
            'published' => 0,
            'draft' => 0,
            'archived' => 0,
        ];
        
        foreach ($articles as $article) {
            $status = $article->getStatus()->value;
            if ($status === 'published') {
                $stats['published']++;
            } elseif ($status === 'draft') {
                $stats['draft']++;
            } elseif ($status === 'archived') {
                $stats['archived']++;
            }
        }
        
        return $this->render('admin/blog/index.html.twig', [
            'articles' => $articles,
            'stats' => $stats,
        ]);
    }


    #[Route('/admin/blog/new', name: 'article_new')]
    #[IsGranted('ROLE_PHOTOGRAPHER')]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger, MediaUploader $mediaUploader): Response
    {
        $article = new Article();
        $user = $this->getUser();
 

        $form = $this->createForm(ArticleFormType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Le status vient du formulaire - c'est tout!
            $article->setAuthor($this->getUser());
            $article->setSlug($slugger->slug($article->getTitle())->lower());
            
            $em->persist($article);
            $em->flush(); // Génère l'ID de l'article

            // EDITOR JS
            $editorContent = $form->get('content')->getData();
            if ($editorContent) {
                $contentData = json_decode($editorContent, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    
                    $file = $form->get('coverFile')->getData();
                    $altText = $form->get('coverAlt')->getData();

                    if ($file) {
                        $media = $mediaUploader->upload(
                            $file,
                            '',
                            $altText,
                            MediaType::ARTICLE_COVER,
                            '/articles/' . $article->getId()
                        );
                        $media->setArticle($article);
                        $em->persist($media);
                        $em->flush();
                    }
                            

                   // Supprime les URLs des blocs "image" et lie les médias à l'article
                    foreach ($contentData['blocks'] as &$block) {
                        if ($block['type'] === 'image' && isset($block['data']['file']['id'])) {
                            // Supprime l'URL du JSON
                            $fileData = $block['data']['file'];
                            $block['data']['file'] = [
                                'id' => $fileData['id'],
                                'width' => $fileData['width'],
                                'height' => $fileData['height'],
                            ];

                            // Lie le média à l'article
                            $media = $em->getRepository(Media::class)->find($fileData['id']);
                            if ($media) {
                                $media->setArticle($article);
                                $media->setAltText($block['data']['alt'] ?? '');
                                $media->setCaption($block['data']['caption'] ?? ''); // Stocke le caption
                                $em->persist($media);
                        }
                    }
                    }
                    // var_dump($editorContent);die;
                    $em->flush(); // Met à jour les médias
        
                    $article->setContent($contentData); // Enregistre le contenu
                    $em->flush(); // Sauvegarde finale
                }
            }




            // var_dump([
            //     'id' => $article->getId(),
            //     'title' => $article->getTitle(),
            //     'metaTitle' => $article->getMetaTitle(),
            //     'metaDescription' => $article->getMetaDescription(),
            //     'excerpt' => $article->getExcerpt(),
            //     'status' => $article->getStatus(),
            //     'createdAt' => $article->getCreatedAt(),
            //     'slug' => $article->getSlug(),
            //     'category' => $article->getCategory() ? $article->getCategory()->getName() : null,
            // ]);
            // die;
            
            
            // dd($article); // Décommentez cette ligne pour voir l'objet final
            // var_dump(get_object_vars($article));die;
            // dump($form->getErrors(true));die;
            // dump($article->getContent());
            // die('Contenu sauvegardé (voir dump ci-dessus)');
     
            // var_dump($article);die;
            // var_dump(get_object_vars($article));die;

            // Simple: message selon le status
            if ($article->getStatus() === \App\Enum\ArticleType::PUBLISHED) {
                $this->addFlash('success', 'Article publié avec succès !');
            } else {
                $this->addFlash('success', 'Article enregistré en brouillon !');
            }
            return $this->redirectToRoute('blog_index');
            //     $em->flush();
            //     $this->addFlash('success', 'Article sauvegardé en brouillon.');
            // }
        }
        return $this->render('blog/article/new.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);

    }

    #[Route('/admin/article/{id}/edit', name: 'article_edit')]
    #[IsGranted('ROLE_PHOTOGRAPHER')]
    public function edit(Article $article, Request $request, EntityManagerInterface $em, SluggerInterface $slugger, MediaUploader $mediaUploader): Response
    {
        if (!$this->isGranted('ROLE_PHOTOGRAPHER')) {
                throw $this->createAccessDeniedException('You cannot delete this article');
        }


        $form = $this->createForm(ArticleFormType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $article->setSlug($slugger->slug($article->getTitle())->lower());
            
            $editorContent = $form->get('content')->getData();
            if ($editorContent) {
                $contentData = json_decode($editorContent, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    
                    $file = $form->get('coverFile')->getData();
                    if ($file) {
                        $oldCover = $article->getMedias()->filter(fn($m) => $m->getType() === MediaType::ARTICLE_COVER)->first();
                        if ($oldCover) {
                            $em->remove($oldCover);
                        }

                        $altText = $form->get('coverAlt')->getData();
                        $media = $mediaUploader->upload(
                            $file,
                            '',
                            $altText,
                            MediaType::ARTICLE_COVER,
                            '/articles/' . $article->getId()
                        );
                        $media->setArticle($article);
                        $em->persist($media);
                    }

                    foreach ($contentData['blocks'] as &$block) {
                        if ($block['type'] === 'image' && isset($block['data']['file']['id'])) {
                            $fileData = $block['data']['file'];
                            $block['data']['file'] = [
                                'id' => $fileData['id'],
                                'width' => $fileData['width'],
                                'height' => $fileData['height'],
                            ];

                            $media = $em->getRepository(Media::class)->find($fileData['id']);
                            if ($media) {
                                $media->setArticle($article);
                                $media->setAltText($block['data']['alt'] ?? '');
                                $media->setCaption($block['data']['caption'] ?? '');
                                $em->persist($media);
                            }
                        }
                    }

                    $article->setContent($contentData);
                }
            }

            $em->flush();

            $this->addFlash('success', 'Article mis à jour avec succès !');
            return $this->redirectToRoute('admin_blog_index');
        }

        return $this->render('blog/article/new.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
            'isEdit' => true,
        ]);
    }

    #[Route('/article/{slug}', name: 'article_show')]
    public function show(ArticleRepository $articleRepository, CommentRepository $commentRepository, string $slug, Request $request, EntityManagerInterface $em, CommentSecurityService $CommentSecurityService ): Response
    {   
        $article = $articleRepository->findOneBy(['slug' => $slug]);
        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }
        $content = $article->getContent();
        $previousArticle = $articleRepository->findPreviousArticle($article->getCreatedAt());
        $nextArticle = $articleRepository->findNextArticle($article->getCreatedAt());
        $findRelatedArticles = $articleRepository->findRelatedArticles($article);
        
        $validatedComments = $commentRepository->findBy([
            'article' => $article,
            'parentComment' => NULL,
        ], [
            'createdAt' => 'ASC', 
        ]);

        $comment = new Comment();
        $comment->setArticle($article);
        $comment->setAuthor($this->getUser());

        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);
        

        if ($form->isSubmitted() && $form->isValid()) {

            $limitCheck = $CommentSecurityService->checkRateLimit($request);
            if (!$limitCheck['accepted']) {

                if ($limitCheck['status'] === 'excess') {
                    $this->addFlash('warning',
                        "Vous soumettez trop vite. Réessayez dans quelques instants."
                    );
                    return $this->redirectToRoute('article_show', ['slug' => $article->getSlug()]);
                }

                if ($limitCheck['status'] === 'spam') {
                    $this->addFlash('danger',
                        "Trop de tentatives. Vous devez patienter un peu avant de commenter."
                    );
                    return $this->redirectToRoute('article_show', ['slug' => $article->getSlug()]);
                }

                if ($limitCheck['status'] === 'bot') {
                    return new Response("Accès bloqué.", 429);
                }
            }
            
            $submittedAt = (int)$form->get('submittedAt')->getData();
            $timeCheck = $CommentSecurityService->checkSubmissionTime($submittedAt, $request);
            if (!$timeCheck['valid']) {

                switch ($timeCheck['status']) {
            
                    case 'tampered':
                        // Suspicion de bot → redirection silencieuse
                        return $this->redirectToRoute('article_show', ['slug' => $article->getSlug()]);
            
                    case 'too_fast':
                        // Simple utilisateur trop rapide
                        $this->addFlash('warning', $timeCheck['message']);
                        return $this->redirectToRoute('article_show', ['slug' => $article->getSlug(),]);
                }
            }

            if ($this->getUser()) {
                $comment->setAuthor($this->getUser());
                if (in_array('ROLE_ADMIN', $this->getUser()->getRoles()) || $this->getUser() === $article->getAuthor()) {
                $comment->setIsApproved(true);
                }
            } else {
                $pseudo = $form->get('authorName')->getData();
                $comment->setAuthorName($pseudo);

                // Récupère et hache l'IP pour les utilisateurs non connectés
                $ip = $request->getClientIp();
                $ipHash = hash('sha256', $ip);
                $comment->setIpHash($ipHash);
            }

            $commentContent = $form->get('content')->getData();
            $filteredContent = $CommentSecurityService->filterCommentContent($commentContent);

            $comment->setContent($filteredContent);
            // dd($filteredContent);
            
            $em->persist($comment);
            $em->flush();

            $this->addFlash('success', 'Votre commentaire a été ajouté !');
            return $this->redirectToRoute('article_show', ['slug' => $article->getSlug()]);
        }

        return $this->render('blog/article/show.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
            'comments' => $validatedComments,
            'content' => $content,
            'commentsCount' => $article->getCommentsCount(),
            'previousArticle' => $previousArticle,
            'nextArticle' => $nextArticle,
            'relatedArticles' => $findRelatedArticles,
        ]);
    }

    #[Route('/comment/{id}/approve', name: 'comment_approve', methods: ['POST'])]
    public function approve(Comment $comment, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $comment->setIsApproved(true);
        $em->flush();

        $this->addFlash('success', 'Commentaire approuvé avec succès.');
        return $this->redirectToRoute('article_show', ['slug' => $comment->getArticle()->getSlug()]);
    }

    #[Route('/admin/article/{id}/delete', name: 'article_delete', methods: ['POST'])]
    #[IsGranted('ROLE_PHOTOGRAPHER')]
    public function delete(Article $article, EntityManagerInterface $em): Response
    {
        if (!$this->isGranted('ROLE_PHOTOGRAPHER')) {
            throw $this->createAccessDeniedException('You cannot delete this article');
        }

        $em->remove($article);
        $em->flush();

        $this->addFlash('success', 'This article and its comments have been deleted successfully.');
        return $this->redirectToRoute('admin_blog_index');
    }

    #[Route('/admin/article/{id}/status', name: 'article_update_status', methods: ['POST'])]
    #[IsGranted('ROLE_PHOTOGRAPHER')]
    public function updateStatus(Article $article, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isGranted('ROLE_PHOTOGRAPHER')) {
            throw $this->createAccessDeniedException('You cannot delete this article');
        }

        $newStatus = $request->request->get('status');
        
        try {
            $status = \App\Enum\ArticleType::from($newStatus);
            $article->setStatus($status);
            $em->flush();
            $this->addFlash('success', 'Article status successfully updated.');
        } catch (\ValueError $e) {
            $this->addFlash('danger', 'Invalid Status.');
        }

        return $this->redirectToRoute('admin_blog_index');
    }

    #[Route('/admin/article/{id}/toggle-featured', name: 'article_toggle_featured', methods: ['POST'])]
    #[IsGranted('ROLE_PHOTOGRAPHER')]
    public function toggleFeatured(Article $article, EntityManagerInterface $em): Response
    {
        if (!$this->isGranted('ROLE_PHOTOGRAPHER')) {
            throw $this->createAccessDeniedException('You cannot update this article');
        }

        $article->setIsFeatured(!$article->isFeatured());
        $em->flush();

        $this->addFlash('success', 'Article featured status successfully updated.');
        return $this->redirectToRoute('admin_blog_index');
    }

}