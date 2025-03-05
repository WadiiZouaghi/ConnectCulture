<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrganizerController extends AbstractController
{
    #[Route('/organizer', name: 'app_organizer')]
    public function index(): Response
    {
        return $this->render('organizer/index.html.twig', [
            'controller_name' => 'OrganizerController',
        ]);
    }
}