<?php

namespace App\Controller;

use App\Entity\Evants;
use App\Form\EvantsType; // Importez EvantsType
use App\Repository\EvantsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/evants')]
final class EvantsController extends AbstractController
{
    #[Route(name: 'app_evants_index', methods: ['GET'])]
    public function index(EvantsRepository $evantsRepository): Response
    {
        return $this->render('evants/index.html.twig', [
            'evants' => $evantsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_evants_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $evant = new Evants();
        $form = $this->createForm(EvantsType::class, $evant); // Utilisez EvantsType::class
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($evant);
            $entityManager->flush();

            return $this->redirectToRoute('app_evants_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('evants/new.html.twig', [
            'evant' => $evant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_evants_show', methods: ['GET'])]
    public function show(Evants $evant): Response
    {
        return $this->render('evants/show.html.twig', [
            'evant' => $evant,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_evants_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Evants $evant, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EvantsType::class, $evant); // Utilisez EvantsType::class
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_evants_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('evants/edit.html.twig', [
            'evant' => $evant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_evants_delete', methods: ['POST'])]
    public function delete(Request $request, Evants $evant, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$evant->getId(), $request->request->get('_token'))) {
            $entityManager->remove($evant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_evants_index', [], Response::HTTP_SEE_OTHER);
    }
}