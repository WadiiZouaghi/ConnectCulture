<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventTypeController extends AbstractController
{
    #[Route('/event/type', name: 'app_event_type')]
    public function index(): Response
    {
        return $this->render('event_type/index.html.twig', [
            'controller_name' => 'EventTypeController',
        ]);
    }
}
