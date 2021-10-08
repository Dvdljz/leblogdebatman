<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * Contrôleur de la page d'accueil
     *
     * @Route("/", name="main_home")
     */
    public function home(): Response
    {
        return $this->render('main/home.html.twig');
    }

    /**
     * Page de profil
     * @Route ("/mon-profil/", name="main_profil")
     * @Security("is_granted('ROLE_USER')")
     */
    public function profil(): Response
    {

        return $this->render('main/profil.html.twig');

    }





}
