<?php
namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ContactFormType;
use App\Service\SeoService;
use App\Service\ContactFormHandler;



class HomeController extends AbstractController
{
    #[Route('/home', name: 'home')]
    public function index(ArticleRepository $articleRepo, ContactFormHandler $handler, SeoService $seoService, Request $request ): Response
    {

        // BLOG SECTION 
        $latestArticles = $articleRepo->findPublishedArticlesWithCover(3);

        // CONTACT SECTION
        $form = $this->createForm(ContactFormType::class);
        $result = $handler->handle($form, $request);

        if ($result['success']) {
            $this->addFlash('success', 'Your message has been sent!');
            return $this->redirectToRoute('home');
        }

        if (($result['error'] ?? null) === 'too_fast') {
            $this->addFlash('warning', 'Please wait a few seconds before submitting.');
            return $this->redirectToRoute('home');
        }

        return $this->render('home/index.html.twig', [
            'latestArticles' => $latestArticles, 
            'contactForm' => $form->createView(),
            'meta_description' => $seoService ->getMetaDescription('home'),
            'metaRobots' => $seoService ->getMetaRobots('home'),
        ]);
    }
}
