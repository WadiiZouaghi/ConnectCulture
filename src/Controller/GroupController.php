<?php

namespace App\Controller;

use App\Entity\Group;
use App\Form\GroupType;
use App\Form\GroupSearchType;
use App\Repository\GroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class GroupController extends AbstractController
{
    private GroupRepository $groupRepository;
    private LoggerInterface $logger;

    public function __construct(GroupRepository $groupRepository, LoggerInterface $logger)
    {
        $this->groupRepository = $groupRepository;
        $this->logger = $logger;
    }

    #[Route('/groups', name: 'group_list')]
    public function list(Request $request): Response
    {
        $form = $this->createForm(GroupSearchType::class);
        $form->handleRequest($request);

        $groups = $form->isSubmitted() && $form->isValid()
            ? $this->groupRepository->findByNameLike($form->get('name')->getData() ?? '')
            : $this->groupRepository->findAll();

        return $this->render('group/index.html.twig', [
            'form' => $form->createView(),
            'groups' => $groups,
        ]);
    }

    #[Route('/group/new', name: 'group_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $group = new Group();
        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($group);
            $em->flush();

            return $this->redirectToRoute('group_list');
        }

        return $this->render('group/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/group/{id}/edit', name: 'group_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Group $group, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $group->setUpdatedAt(new \DateTime());
            $em->flush();

            return $this->redirectToRoute('group_list');
        }

        return $this->render('group/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/group/{id}/delete', name: 'group_delete', methods: ['POST'])]
    public function delete(Request $request, Group $group, EntityManagerInterface $entityManager): JsonResponse
    {
        // Log the deletion for debugging purposes
        $this->logger->info("Deleting group with ID: " . $group->getId());
    
        // Check the CSRF token if applicable (for security)
        if ($this->isCsrfTokenValid('delete'.$group->getId(), $request->request->get('_token'))) {
            $entityManager->remove($group);
            $entityManager->flush(); // Commit the changes
            return $this->redirectToRoute('group_list');
        }
    
        // If token is invalid, return an error (security purpose)
        return new JsonResponse(['success' => false, 'message' => 'Invalid CSRF token.']);
    }

    #[Route('/group/{id}/delete/confirm', name: 'group_delete_confirmation', methods: ['GET'])]
    public function deleteConfirmation(Group $group): Response
    {
        return $this->render('group/delete_confirm.html.twig', [
            'group' => $group,
        ]);
    }

    #[Route('/group/{id}', name: 'group_show')]
    public function show(Group $group): Response
    {
        return $this->render('group/show.html.twig', [
            'group' => $group,
        ]);
    }
}
