<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\CommentFormType;
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
    public function publicationView(Article $article, Request $request): Response
    {

        // Si l'user n'est pas connecté, on appelle la vue directement
        if (!$this->getUser()){

            return $this->render('blog/publicationView.html.twig', [
                'article' => $article,
            ]);
        }

        $comment = new Comment();

        $form = $this->createForm(CommentFormType::class, $comment);

        $form->handleRequest($request);

        // Vérifs formulaire
        if ($form->isSubmitted() && $form->isValid()){

        $comment
            ->setPublicationDate(new \DateTime())
            ->setArticle($article)
            ->setAuthor($this->getUser())
        ;

        // Sauvegarde en BDD
            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();

            $this->addFlash('success', 'Votre commentaire a été publié avec succès !');

            // Remise à zéro du formulaire (si jms il y en a un autre)
            unset($comment);
            unset($form);
            $comment = new Comment();
            $form = $this->createForm(CommentFormType::class, $comment);


        }


        return $this->render('blog/publicationView.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
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
                'search' => '%' . $search . '%',
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

    /**
     * Page admin servant à supprimer un article via son id passé dans l'URL
     *
     * @Route ("/publication/supression/{id}/", name="publication_delete")
     * @Security ("is_granted('ROLE_ADMIN')")
     */
    public function publicationDelete(Request $request, Article $article): Response
    {

        if(!$this->isCsrfTokenValid('blog_publication_delete_' . $article->getId(), $request->query->get('csrf_token'))){

            $this->addFlash('error', 'Token de sécurité invalide, veuillez ré-essayer.');
        } else {

            // Manager général
            $em = $this->getDoctrine()->getManager();

            // Suppression de l'article
            $em->remove($article);
            $em->flush();

            // Message flash de succès + redirection sur la liste des articles
            $this->addFlash('success', 'La publication a été supprimée avec succès !');
        }
        return $this->redirectToRoute('blog_publication_list');

    }


    /**
     * Page permettant aux admins de modifier un article
     * @Route ("/publication/modifier/{id}/", name="publication_edit")
     * @Security ("is_granted('ROLE_ADMIN')")
     */
    public function publicationEdit(Article $article, Request $request): Response
    {

        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        // Si formulaire ok
        if ($form->isSubmitted() && $form->isValid()){

            // Mise à jour dans la BDD
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'Publication modifiée avec succès !');

            return $this->redirectToRoute('blog_publication_view', [
                'slug' => $article->getSlug(),
            ]);
        }

        return $this->render('blog/publicationEdit.html.twig', [
            'form' => $form->createView(),
        ]);

    }


    /**
     * Page permettant la suppression d'un commenatire par un admin
     * @Route ("/commentaire/suppression/{id}/", name="comment_delete")
     * @Security ("is_granted('ROLE_ADMIN')")
     */
    public function commentDelete(Comment $comment, Request $request): Response
    {

        // Si le token CSRF passé dans l'URL n'est pas valide
        if (!$this->isCsrfTokenValid('blog_comment_delete' . $comment->getId(), $request->query->get('csrf_token'))){
            $this->addFlash('error', 'Token sécurité invalide, veuillez réessayer.');
        } else {

            // Suppression du commentaire en BDD
            $em = $this->getDoctrine()->getManager();
            $em->remove($comment);
            $em->flush();

            $this->addFlash('success', 'Le commentaire a été supprimé avec succès !');

        }

        // Redirection sur la page de l'article auquel était rattaché le commentaire
        return $this->redirectToRoute('blog_publication_view', [
            'slug' => $comment->getArticle()->getSlug(),
            ]);

    }


}