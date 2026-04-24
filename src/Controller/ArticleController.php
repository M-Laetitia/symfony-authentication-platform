<?php 
namespace App\Controller;

error_reporting(E_ALL);
ini_set('display_errors', 1);

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Media;
use App\Entity\Tag;
use App\Enum\MediaType;
use App\Form\ArticleFilterType;
use App\Form\ArticleFormType;
use App\Form\CategoryFormType;
use App\Form\CommentFormType;
use App\Form\AdminCommentFormType;
use App\Form\SearchArticleFormType;
use App\Form\TagFormType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\TagRepository;
use App\Service\CommentSecurityService;
use App\Service\MediaUploader;
use App\Service\SeoService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Form\FormError;



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

    #[Route('/admin/blog', name: 'admin_blog_index')]
    #[IsGranted('ROLE_PHOTOGRAPHER')]
    public function adminIndex(ArticleRepository $articleRepo, CategoryRepository $categoryRepo, TagRepository $tagRepo, CommentRepository $commentRepository, PaginatorInterface $paginator, Request $request): Response
    {
        // Get all categories for the filter dropdown
        $categories = $categoryRepo->findAll();
        $tags = $tagRepo->findAll();
        
        // create choices array for the form
        $categoryChoices = [];
        foreach ($categories as $category) {
            $categoryChoices[$category->getName()] = (string)$category->getId();
        }
        
        // create form with submitted data or defaults
        $filterData = [
            'search' => $request->query->get('search', ''),
            'sortBy' => $request->query->get('sortBy', 'date_desc'),
            'status' => $request->query->get('status', ''),
            'featured' => $request->query->get('featured', ''),
            'category' => $request->query->get('category', ''),
        ];
        
        $filterForm = $this->createForm(ArticleFilterType::class, $filterData, [
            'category_choices' => $categoryChoices,
        ]);
        
        // extract filter values for repository method
        $search = $filterData['search'];
        $sortBy = $filterData['sortBy'];
        $status = $filterData['status'];
        $featured = $filterData['featured'];
        $categoryId = $filterData['category'];
        
        // Get filtered articles for pagination and display
        $allArticles = $articleRepo->findAllForAdminFiltered($sortBy, $status, $featured, $categoryId, $search);
        
        // Get ALL articles (without filters) for global statistics
        $allArticlesForStats = $articleRepo->findAllForAdmin();
        
        // Calculate stats based on ALL articles, not filtered ones
        $stats = [
            'total' => count($allArticlesForStats),
            'published' => 0,
            'draft' => 0,
            'archived' => 0,
        ];
        
        foreach ($allArticlesForStats as $article) {
            $articleStatus = $article->getStatus()->value;
            if ($articleStatus === 'published') {
                $stats['published']++;
            } elseif ($articleStatus === 'draft') {
                $stats['draft']++;
            } elseif ($articleStatus === 'archived') {
                $stats['archived']++;
            }
        }
        
        $pagination = $paginator->paginate(
            $allArticles,
            $request->query->getInt('page', 1),
            12
        );
        
        // Get unapproved comments
        $unapprovedComments = $commentRepository->findBy(['isApproved' => false], ['createdAt' => 'DESC']);
        
        return $this->render('admin/blog/index.html.twig', [
            'articles' => $pagination,
            'stats' => $stats,
            'filterForm' => $filterForm->createView(),
            'filters' => [
                'search' => $search,
                'sortBy' => $sortBy,
                'status' => $status,
                'featured' => $featured,
                'category' => $categoryId,
            ],
            'categories' => $categories,
            'tags' => $tags,
            'unapprovedComments' => $unapprovedComments,
        ]);
    }


    #[Route('/admin/blog/new', name: 'article_new')]
    #[IsGranted('ROLE_PHOTOGRAPHER')]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger, MediaUploader $mediaUploader): Response
    {
        $article = new Article();
 
        $form = $this->createForm(ArticleFormType::class, $article, [
            'is_edit' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $article->setAuthor($this->getUser());
            // $article->setSlug($slugger->slug($article->getTitle())->lower());

            // unique slug generation
            $baseSlug = $slugger->slug($article->getTitle())->lower();
            $slug = $baseSlug;
            $i = 1;
            while ($em->getRepository(Article::class)->findOneBy(['slug' => $slug])) {
                $slug = $baseSlug . '-' . $i++;
            }
            $article->setSlug($slug);
            
            $em->persist($article);
            $em->flush(); 

            // try {
            //     $em->flush();
            // } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            //     $form->get('title')->addError(new FormError('This title is already used.'));
            // }

            // EDITOR JS
            $editorContent = $form->get('content')->getData();
            if ($editorContent) {
                $contentData = json_decode($editorContent, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    
                    $file = $form->get('coverFile')->getData();
                    $altText = $form->get('coverAlt')->getData() ?? '';

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

                            // Bind media to article
                            $media = $em->getRepository(Media::class)->find($fileData['id']);
                            if ($media) {
                                $media->setArticle($article);
                                $media->setAltText($block['data']['alt'] ?? '');
                                $media->setCaption($block['data']['caption'] ?? ''); 
                                $em->persist($media);
                            }
                        }
                    }
                    $em->flush();
        
                    $article->setContent($contentData); 
                    $em->flush(); 
                }
            }

            if ($article->getStatus() === \App\Enum\ArticleType::PUBLISHED) {
                $this->addFlash('success', 'Article published successfully!');
            } else {
                $this->addFlash('success', 'Article saved as draft!');
            }
            return $this->redirectToRoute('admin_blog_index');

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

        $form = $this->createForm(ArticleFormType::class, $article, [
            'is_edit' => true,
        ]);
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

                        $altText = $form->get('coverAlt')->getData() ?? '';
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

            $this->addFlash('success', 'Article successfully updated!');
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

            $this->addFlash('success', 'Your comment has been successfully posted !');
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
    public function delete(Article $article, EntityManagerInterface $em, MediaUploader $mediaUploader): Response
    {
        if (!$this->isGranted('ROLE_PHOTOGRAPHER')) {
            throw $this->createAccessDeniedException('You cannot delete this article');
        }

        // Delete all associated media files
        foreach ($article->getMedias() as $media) {
            $mediaUploader->deleteMediaFile($media);
        }

        // Deletet EditorJS images that are not referenced anymore (in case some were left orphaned)
        $this->deleteArticleEditorImages($article);

        // Delete article folder if exists (for any additional files that might be there)
        $this->deleteArticleFolder($article->getId());

        $em->remove($article);
        $em->flush();

        $this->addFlash('success', 'This article and its comments have been deleted successfully.');
        return $this->redirectToRoute('admin_blog_index');
    }

    /**
     * Delete EditorJS images associated with the article
     */
    private function deleteArticleEditorImages(Article $article): void
    {
        try {
            // Delete files of all associated ARTICLE_IMAGE medias
            foreach ($article->getMedias() as $media) {
                $filePath = $media->getPath();
                if (!$filePath) {
                    continue;
                }

                // Build the absolute path
                $absolutePath = $this->getParameter('kernel.project_dir') . '/public' . $filePath;
                
                // @unlink the principal file
                if (file_exists($absolutePath)) {
                    @unlink($absolutePath);
                }

                // Delete .webp variant (generated by LiipImagine)
                if (str_ends_with($absolutePath, '.webp')) {
                    $basePath = substr($absolutePath, 0, -5); 
                    foreach (['.jpg', '.png', '.jpeg'] as $ext) {
                        $variantPath = $basePath . $ext;
                        if (file_exists($variantPath)) {
                            @unlink($variantPath);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Log the error but don't block article deletion
            $this->addFlash('warning', 'Article deleted but some media files could not be removed.');
        }
    }

    /**
     * Delete the article's folder (uploads/articles/{id})
     */
    private function deleteArticleFolder(int $articleId): void
    {
        try {
            $uploadsPath = $this->getParameter('kernel.project_dir') . '/public/uploads/articles/' . $articleId;
            
            if (is_dir($uploadsPath)) {
                $this->deleteDirectoryRecursive($uploadsPath);
            }
        } catch (\Exception $e) {
            $this->addFlash('warning', 'Article deleted but some files could not be removed.');
        }
    }

    /**
     * Delete a directory recursively
     */
    private function deleteDirectoryRecursive(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        foreach (scandir($path) as $file) {
            if ($file !== '.' && $file !== '..') {
                $filePath = $path . '/' . $file;
                
                if (is_dir($filePath)) {
                    $this->deleteDirectoryRecursive($filePath);
                } else {
                    @unlink($filePath);
                }
            }
        }

        @rmdir($path);
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

    // ============= CATEGORY MANAGEMENT =============

    #[Route('/admin/blog/categories/new', name: 'category_new')]
    #[IsGranted('ROLE_PHOTOGRAPHER')]
    public function newCategory(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category->setSlug($slugger->slug($category->getName())->lower());
            $em->persist($category);
            $em->flush();

            $this->addFlash('success', 'Category created successfully.');
            return $this->redirectToRoute('admin_blog_index');
        }

        return $this->render('admin/blog/category_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Create Category',
        ]);
    }

    #[Route('/admin/blog/categories/{id}/edit', name: 'category_edit')]
    #[IsGranted('ROLE_PHOTOGRAPHER')]
    public function editCategory(Category $category, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category->setSlug($slugger->slug($category->getName())->lower());
            $em->flush();

            $this->addFlash('success', 'Category updated successfully.');
            return $this->redirectToRoute('admin_blog_index');
        }

        return $this->render('admin/blog/category_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Edit Category',
            'category' => $category,
        ]);
    }

    #[Route('/admin/blog/categories/{id}/delete', name: 'category_delete', methods: ['POST'])]
    #[IsGranted('ROLE_PHOTOGRAPHER')]
    public function deleteCategory(Category $category, EntityManagerInterface $em): Response
    {
        // Check if category has articles
        if (count($category->getArticles()) > 0) {
            $this->addFlash('error', 'Cannot delete this category because it has ' . count($category->getArticles()) . ' article(s) linked to it. Please reassign or delete the articles first.');
        } else {
            $em->remove($category);
            $em->flush();
            $this->addFlash('success', 'Category deleted successfully.');
        }

        return $this->redirectToRoute('admin_blog_index');
    }

    // ============= TAG MANAGEMENT =============

    #[Route('/admin/blog/tags/new', name: 'tag_new')]
    #[IsGranted('ROLE_PHOTOGRAPHER')]
    public function newTag(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $tag = new Tag();
        $form = $this->createForm(TagFormType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tag->setSlug($slugger->slug($tag->getName())->lower());
            $em->persist($tag);
            $em->flush();

            $this->addFlash('success', 'Tag created successfully.');
            return $this->redirectToRoute('admin_blog_index');
        }

        return $this->render('admin/blog/tag_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Create Tag',
        ]);
    }

    #[Route('/admin/blog/tags/{id}/edit', name: 'tag_edit')]
    #[IsGranted('ROLE_PHOTOGRAPHER')]
    public function editTag(Tag $tag, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(TagFormType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tag->setSlug($slugger->slug($tag->getName())->lower());
            $em->flush();

            $this->addFlash('success', 'Tag updated successfully.');
            return $this->redirectToRoute('admin_blog_index');
        }

        return $this->render('admin/blog/tag_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Edit Tag',
            'tag' => $tag,
        ]);
    }

    #[Route('/admin/blog/tags/{id}/delete', name: 'tag_delete', methods: ['POST'])]
    #[IsGranted('ROLE_PHOTOGRAPHER')]
    public function deleteTag(Tag $tag, EntityManagerInterface $em): Response
    {
        // Check if tag has articles
        if (count($tag->getArticles()) > 0) {
            $this->addFlash('error', 'Cannot delete this tag because it has ' . count($tag->getArticles()) . ' article(s) linked to it. Please remove the tag from articles first.');
        } else {
            $em->remove($tag);
            $em->flush();
            $this->addFlash('success', 'Tag deleted successfully.');
        }

        return $this->redirectToRoute('admin_blog_index');
    }

    // ============= COMMENT MANAGEMENT =============

    #[Route('/admin/blog/comments/{id}/approve', name: 'comment_approve', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function approveComment(Comment $comment, EntityManagerInterface $em): Response
    {
        $comment->setIsApproved(true);
        $em->flush();

        $this->addFlash('success', 'Comment approved successfully.');
        return $this->redirectToRoute('admin_blog_index');
    }

    #[Route('/admin/blog/comments/{id}/edit', name: 'comment_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function editComment(Comment $comment, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(AdminCommentFormType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setEditedAt(new \DateTimeImmutable());
            $comment->setLastEditBy($this->getUser());
            $em->flush();

            $this->addFlash('success', 'Comment updated successfully.');
            
            // Redirection based on context (check both query AND request)
            $redirectTo = $request->request->get('redirect_to') ?? $request->query->get('redirect_to');
            
            if ($redirectTo === 'article') {
                return $this->redirectToRoute('article_show', [
                    'slug' => $comment->getArticle()->getSlug()
                ]);
            }
            
            return $this->redirectToRoute('admin_blog_index');
        }

        return $this->render('admin/blog/comment_edit.html.twig', [
            'form' => $form->createView(),
            'comment' => $comment,
        ]);
    }

    #[Route('/admin/blog/comments/{id}/delete', name: 'comment_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteComment(Comment $comment, EntityManagerInterface $em, Request $request): Response
    {
        $redirectTo = $request->query->get('redirect_to', 'admin');
        $em->remove($comment);
        $em->flush();

        $this->addFlash('success', 'Comment deleted successfully.');
            
        // Redirection based on context (check both query AND request)
        if ($redirectTo === 'article') {
            return $this->redirectToRoute('article_show', ['slug' => $comment->getArticle()->getSlug()]);
        }
        
        return $this->redirectToRoute('admin_blog_index');
    }

}
