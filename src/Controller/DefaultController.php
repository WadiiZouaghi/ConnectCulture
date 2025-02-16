<?php

// src/Controller/DefaultController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        // Affiche la page d'accueil (par exemple, home/index.html.twig)
        return $this->render('home/index.html.twig');
    }

    /**
     * @Route("/about", name="about")
     */
    public function about(): Response
    {
        // Affiche la page "about"
        return $this->render('home/about.html.twig');
    }
}
