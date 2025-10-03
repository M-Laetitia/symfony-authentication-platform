<?php 
namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleFormType;
use App\Form\CommentFormType;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Repository\ArticleRepository;

class ArticleController extends AbstractController
{
    #[Route('/blog', name: 'blog_index')]
    public function index(EntityManagerInterface $em): Response
    {
        // $articles = $em->getRepository(Article::class)->findAll();
        $publishedArticles = $em->getRepository(Article::class)->findBy([
            'status' => 'published'
        ], [
            'createdAt' => 'DESC' // Tri par date de création (optionnel)
        ]);

        return $this->render('blog/article/index.html.twig', [
            'articles' => $publishedArticles,
        ]);
    }

    #[Route('/blog/new', name: 'article_new')]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $article = new Article();
        $user = $this->getUser();
        // var_dump($user);die;
                // dump($form->getName());die;
        // dump($request->getMethod());
        // dump($request->request->all());
        // dump($form->getName());die;
        
        // dump($request->request->all())
        // if ($form->isSubmitted()) {
        //     dump('Form is submitted');
        //     dump($form->getErrors(true));die;
        // }

        $form = $this->createForm(ArticleFormType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $editorContent = $form->get('content')->getData();
            dump('Contenu reçu:', $editorContent);
            dump('Type:', gettype($editorContent));
            if ($editorContent) {
                $contentData = json_decode($editorContent, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $article->setContent($contentData);
                } else {
                    $this->addFlash('error', 'Erreur dans le contenu de l\'article');
                    return $this->render('blog/article/new.html.twig', [
                        'form' => $form->createView(),
                        'article' => $article,
                    ]);
                }
            }
            // var_dump($editorContent);die;

            $article->setAuthor($user);
            $article->setSlug($slugger->slug($article->getTitle())->lower());
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
            $em->persist($article);
            $em->flush();
            // var_dump($article);die;
            // var_dump(get_object_vars($article));die;

            return $this->redirectToRoute('blog_index');

            
            // if ($article->getStatus() === 'draft') {
            //     // Si c'est un brouillon, on le publie et on redirige vers la liste
            //     $article->setStatus('published');
            //     $em->persist($article);
            //     $em->flush();
            //     $this->addFlash('success', 'Article publié avec succès !');
            //     return $this->redirectToRoute('blog_index'); // Redirection vers la liste
            // } else {
            //     // Sinon, on le repasse en brouillon et on reste sur la page
            //     $article->setStatus('draft');
            //     $em->persist($article);
            //     $em->flush();
            //     $this->addFlash('success', 'Article sauvegardé en brouillon.');
            // }
        }

        return $this->render('blog/article/new.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);

    }

    #[Route('/article/{slug}', name: 'article_show')]
    public function show(Article $article, ArticleRepository $articleRepository, CommentRepository $commentRepository, string $slug, Request $request, EntityManagerInterface $em): Response
    {   

        $article = $articleRepository->findOneBy(['slug' => $slug]);
        $validatedComments = $commentRepository->findBy([
            'article' => $article,
            'isApproved' => true, 
        ], [
            'createdAt' => 'ASC', 
        ]);

        if (!$article) {
            throw $this->createNotFoundException('Article non trouvé');
        }

        $comment = new Comment();
        $comment->setArticle($article);
        $comment->setAuthor($this->getUser());

        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // var_dump($comment);die;
            $em->persist($comment);
            $em->flush();

            $this->addFlash('success', 'Votre commentaire a été ajouté !');
            return $this->redirectToRoute('article_show', ['slug' => $article->getSlug()]);
        }

        return $this->render('blog/article/show.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
            'comments' => $validatedComments,
        ]);
    }
}