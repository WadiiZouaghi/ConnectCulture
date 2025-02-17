<?php

namespace App\Controller;

use App\Entity\Competition;
use App\Form\CompetitionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CompetitionController extends AbstractController
{
    #[Route('/competition', name: 'competition_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $competition = new Competition();
        $form = $this->createForm(CompetitionType::class, $competition);
    
        $form->handleRequest($request); // Only call this once
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($competition);
            $entityManager->flush();
            $this->addFlash('success', 'Competition ajoutée avec succès !');
            return $this->render('/front/index.html.twig');
            }
    
        return $this->render('competition.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/competitionadmin', name: 'competition_newadmin')]
    public function newadmin(Request $request, EntityManagerInterface $entityManager): Response
    {
        $competition = new Competition();
        $form = $this->createForm(CompetitionType::class, $competition);
    
        $form->handleRequest($request); // Only call this once
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($competition);
            $entityManager->flush();
            $this->addFlash('success', 'Competition ajoutée avec succès !');
            return $this->render('/front/index.html.twig');
            }
    
        return $this->render('competitionadmin.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/', name: 'app_frontcompetition')]
    public function index(): Response
    {
        return $this->render('/front/index.html.twig', [
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
    #[Route('/competition/success', name: 'competition_success')]
    public function success(): Response
    {
        return new Response('<h1>La compétition a été ajoutée avec succès !</h1>');
    }
    #[Route('/admin/tables', name: 'competition_list')]
public function list(EntityManagerInterface $entityManager): Response
{
    $competitions = $entityManager->getRepository(Competition::class)->findAll();

    return $this->render('/back/pages/tables/basic-table.html.twig', [
        'competitions' => $competitions,
    ]);
}
#[Route('/delete/{idComp}', name: 'supp_competition')]
public function deleteCompetition(int $idComp, EntityManagerInterface $entityManager): Response
{
    $competition = $entityManager->getRepository(Competition::class)->find($idComp);

    if (!$competition) {
        throw $this->createNotFoundException('No competition found for id ' . $idComp);
    }

    $entityManager->remove($competition);
    $entityManager->flush();

    return $this->redirectToRoute('competition_list');
}

    #[Route('/admin/tables/{idComp}', name: 'update_competition')]
    public function update(int $idComp, Request $request, EntityManagerInterface $entityManager): Response
    {
        $competition = $entityManager->getRepository(Competition::class)->find($idComp);

        if (!$competition) {
            throw $this->createNotFoundException('No competition found for id ' . $idComp);
        }

        $form = $this->createForm(CompetitionType::class, $competition);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Competition updated successfully!');
            return $this->redirectToRoute('competition_list');
        }

        return $this->render('updatecompetition.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
