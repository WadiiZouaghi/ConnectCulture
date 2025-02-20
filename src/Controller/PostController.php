<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PanierController extends AbstractController
{
    #[Route('/post', name: 'app_panier')]
    public function index(): Response
    {
        return $this->render('post/index.html.twig', [
            'controller_name' => 'PanierController',
        ]);
    }
}
