<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route ("/blog", name="blog_")
 */
class BlogController extends AbstractController
{
    /**
     * @Route("/nouvelle-publication/", name="new_publication", methods={"GET","POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     * Page admin permettant de créer une nouvelle publication
     */
    public function newPublication(Request $request): Response
    {

        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $article->setAuthor($this->getUser());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($article);
            $entityManager->flush();

            // Création d'un flash message de type "success"
            $this->addFlash('success', 'Article créé avec succès !');

            return $this->redirectToRoute('blog_publication_view', [
                'slug' => $article->getSlug(),
            ]);
        }

        return $this->renderForm('blog/newPublication.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);

    }

    /**
     * @Route("/publications/liste/", name="publication_list", methods={"GET"})
     */
    public function publicationList(Request $request, ArticleRepository $articleRepository, PaginatorInterface $paginator): Response
    {

        // Récupération du numéro de la page demandée dans l'URL
        $requestedPage = $request->query->getInt('page', 1);

        // Vérification que le numéro est positif
        if($requestedPage < 1){
            throw new NotFoundHttpException();
        }

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery('SELECT a FROM App\Entity\Article a ORDER BY a.publicationDate DESC');

        // Récupération des articles
        $articles = $paginator->paginate(
            $query,
            $requestedPage,
            10
        );


        return $this->render('blog/publicationList.html.twig', [
            'articles' => $articles,
        ]);
    }


    /**
     * @Route("/publication/{slug}", name="publication_view")
     * Page permettant de voir un article en détail
     */
    public function publicationView(Article $article): Response
    {
        return $this->render('blog/publicationView.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * @Route ("/recherche/", name="search")
     */
    public function search(Request $request, PaginatorInterface $paginator): Response
    {

        // Récupération du numéro de la page demandée dans l'URL
        $requestedPage = $request->query->getInt('page', 1);
        // Vérification que le numéro est positif
        if($requestedPage < 1){
            throw new NotFoundHttpException();
        }

        $search = $request->query->get('search', '');

        $em = $this->getDoctrine()->getManager();

        $query = $em
            ->createQuery('SELECT a FROM App\Entity\Article a WHERE a.title LIKE :search OR a.content LIKE :search ORDER BY a.publicationDate DESC')
            ->setParameters([
                'search' => '%' . 'search' . '%',
            ])
        ;

        // Récupération des articles
        $articles = $paginator->paginate(
            $query,
            $requestedPage,
            10
        );


        return $this->render('blog/listSearch.html.twig', [
            'articles' => $articles,
        ]);




    }

}