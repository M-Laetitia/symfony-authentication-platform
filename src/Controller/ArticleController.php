<?php 
namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ArticleController extends AbstractController
{
    #[Route('/blog', name: 'blog_index')]
    public function index(EntityManagerInterface $em): Response
    {
        // $articles = $em->getRepository(Article::class)->findAll();

        return $this->render('blog/article/index.html.twig', [
            // 'articles' => $articles,
        ]);
    }

    #[Route('/blog/new', name: 'article_new')]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $article = new Article();
        $user = $this->getUser();
        // var_dump($user);die;


        $form = $this->createForm(ArticleFormType::class, $article);
        $form->handleRequest($request);
        // dump($form->getName());die;
        // dump($request->getMethod());
        // dump($request->request->all());
        // dump($form->getName());die;
        
        // dump($request->request->all())
        // if ($form->isSubmitted()) {
        //     dump('Form is submitted');
        //     dump($form->getErrors(true));die;
        // }

        if ($form->isSubmitted() && $form->isValid()) {
            
            $editorContent = $form->get('content')->getData();
            if ($editorContent) {
                $contentData = json_decode($editorContent, true);
                $article->setContent(json_last_error() === JSON_ERROR_NONE ? $contentData : []);
            }
            var_dump($editorContent);die;

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

            // var_dump(get_object_vars($article));die;
            // dump($form->getErrors(true));die;
            $em->persist($article);
            $em->flush();
            // var_dump($article);die;
            // var_dump(get_object_vars($article));die;

            return $this->redirectToRoute('blog_index');
        }

        return $this->render('blog/article/new.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);

    }
}