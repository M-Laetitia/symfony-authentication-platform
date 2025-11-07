<?php 
namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Media;
use App\Service\MediaUploader;
use App\Form\ArticleFormType;
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
use Symfony\Component\VarDumper\VarDumper;

class ArticleController extends AbstractController
{
    #[Route('/blog', name: 'blog_index')]
    public function index(EntityManagerInterface $em, ArticleRepository $articleRepo): Response
    {
        // $articles = $em->getRepository(Article::class)->findAll();
        // $publishedArticles = $em->getRepository(Article::class)->findBy([
        //     'status' => 'published'
        // ], [
        //     'createdAt' => 'DESC' 
        // ]);

        // return $this->render('blog/article/index.html.twig', [
        //     'articles' => $publishedArticles,
        // ]);

        $articles = $articleRepo->findPublishedArticlesWithCover();
        // dd($articles);

        return $this->render('blog/article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/blog/new', name: 'article_new')]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger, MediaUploader $mediaUploader): Response
    {
        $article = new Article();
        $user = $this->getUser();
 

        $form = $this->createForm(ArticleFormType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $form->get('coverFile')->getData();
            $altText = $form->get('coverAlt')->getData();

            // EDITOR JS
            $editorContent = $form->get('content')->getData();
            // dump('Contenu reçu:', $editorContent);die;
            // dump('Type:', gettype($editorContent));
            if ($editorContent) {
                $contentData = json_decode($editorContent, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    // Enregistre l'article d'abord pour obtenir son ID
                    $article->setAuthor($this->getUser());
                    $article->setSlug($slugger->slug($article->getTitle())->lower());
                    $em->persist($article);
                    $em->flush(); // Génère l'ID de l'article

                    
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

            $this->addFlash('success', 'Article créé avec succès !');
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
    public function show(ArticleRepository $articleRepository, CommentRepository $commentRepository, string $slug, Request $request, EntityManagerInterface $em): Response
    {   

        $article = $articleRepository->findOneBy(['slug' => $slug]);
        if (!$article) {
            throw $this->createNotFoundException('Article non trouvé');
        }
        $validatedComments = $commentRepository->findBy([
            'article' => $article,
            'isApproved' => true, 
            'parentComment' => NULL,
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
            // $data = $form->getData();
            // var_dump($data);die;

            // var_dump([
            //     'content' => $comment->getContent(),
            //     'authorName' => $comment->getAuthorName(),
          
            // ]);
            // die;
            // var_dump($comment);die;

            // entité hydraté - récursion d'objet liés getOne - limité à la première entit" qu'il va trouver 
            // $request > $request fetch all > array
  
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