<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

            return $this->redirectToRoute('main_home');
        }

        return $this->renderForm('blog/newPublication.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);

    }

    /**
     * @Route("/publications/liste/", name="publication_list", methods={"GET"})
     */
    public function publicationList(ArticleRepository $articleRepository): Response
    {
        return $this->render('blog/publicationList.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    /**
     * @Route("/publication/{id}", name="publication_view")
     */
    public function publicationView(Article $article): Response
    {
        return $this->render('blog/publicationView.html.twig', [
            'article' => $article,
        ]);
    }



}