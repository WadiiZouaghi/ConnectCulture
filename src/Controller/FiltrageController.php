<?php

namespace App\Controller;

use App\Entity\Filtrage;
use App\Form\FiltrageType;
use App\Repository\FiltrageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/filtrage')]
final class FiltrageController extends AbstractController
{
    #[Route(name: 'app_filtrage_index', methods: ['GET'])]
    public function index(FiltrageRepository $filtrageRepository): Response
    {
        return $this->render('filtrage/index.html.twig', [
            'filtrages' => $filtrageRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_filtrage_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $filtrage = new Filtrage();
        $form = $this->createForm(FiltrageType::class, $filtrage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($filtrage);
            $entityManager->flush();

            return $this->redirectToRoute('app_filtrage_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('filtrage/new.html.twig', [
            'filtrage' => $filtrage,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_filtrage_show', methods: ['GET'])]
    public function show(Filtrage $filtrage): Response
    {
        return $this->render('filtrage/show.html.twig', [
            'filtrage' => $filtrage,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_filtrage_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Filtrage $filtrage, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FiltrageType::class, $filtrage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_filtrage_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('filtrage/edit.html.twig', [
            'filtrage' => $filtrage,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_filtrage_delete', methods: ['POST'])]
    public function delete(Request $request, Filtrage $filtrage, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$filtrage->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($filtrage);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_filtrage_index', [], Response::HTTP_SEE_OTHER);
    }
}
