<?php

namespace App\Controller;

use App\Entity\Agency;
use App\Form\AgencyType;
use App\Repository\AgencyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/agency')]
class AgencyController extends AbstractController
{
    #[Route('/', name: 'agency_index', methods: ['GET'])]
    public function index(AgencyRepository $agencyRepository): Response
    {
        return $this->render('agency/index.html.twig', [
            'agencies' => $agencyRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'agency_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $agency = new Agency();
        $form = $this->createForm(AgencyType::class, $agency);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($agency);
            $em->flush();

            return $this->redirectToRoute('agency_index');
        }

        return $this->render('agency/new.html.twig', [
            'agency' => $agency,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'agency_show', methods: ['GET'])]
    public function show(Agency $agency): Response
    {
        return $this->render('agency/show.html.twig', [
            'agency' => $agency,
        ]);
    }

    #[Route('/{id}/edit', name: 'agency_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Agency $agency, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(AgencyType::class, $agency);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('agency_index');
        }

        return $this->render('agency/edit.html.twig', [
            'agency' => $agency,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'agency_delete', methods: ['POST'])]
    public function delete(Request $request, Agency $agency, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $agency->getId(), $request->request->get('_token'))) {
            $em->remove($agency);
            $em->flush();
        }

        return $this->redirectToRoute('agency_index');
    }
}
