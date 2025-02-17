<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CompetitionController extends AbstractController
{
    #[Route('/', name: 'app_frontcompetition')]
    public function index(): Response
    {
        return $this->render('/front/index.html.twig', [
            'controller_name' => 'CompetitionController',
        ]);
    }
    #[Route('/competition', name: 'competition')]
    public function competition(): Response
    {
        return $this->render('/front/competition.html.twig', [
            'controller_name' => 'CompetitionController',
        ]);
    }
    #[Route('/admin', name: 'app_backcompetition')]
    public function back(): Response
    {
        return $this->render('/back/index.html.twig', [
            'controller_name' => 'CompetitionController',
        ]);
    }
    #[Route('/admin/tables', name: 'app_backtablecompetition')]
    public function backtable(): Response
    {
        return $this->render('/back/pages/tables/basic-table.html.twig', [
            'controller_name' => 'CompetitionController',
        ]);
    }

}
