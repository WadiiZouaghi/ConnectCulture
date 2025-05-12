<?php

namespace App\Controller;

use App\Entity\Group;
use App\Form\GroupType;
use App\Repository\GroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route('/group')]
final class GroupController extends AbstractController
{
    private function getSystemInfo(): array
    {
        return [
            'current_time' => new \DateTime('now', new \DateTimeZone('UTC')),
            'current_user' => $this->getUser(),
        ];
    }
    
    #[Route('/dashboard', name: 'app_group_dashboard', methods: ['GET'])]
    public function dashboard(GroupRepository $groupRepository): Response
    {
        $groups = $groupRepository->findAll();
        $groupCount = count($groups);
        
        // Get counts by category
        $categoryCounts = [];
        foreach ($groups as $group) {
            $category = $group->getCategory() ?? 'Uncategorized';
            if (!isset($categoryCounts[$category])) {
                $categoryCounts[$category] = 0;
            }
            $categoryCounts[$category]++;
        }
        
        // Get counts by visibility
        $visibilityCounts = [];
        foreach ($groups as $group) {
            $visibility = $group->getVisibility() ?? 'Unknown';
            if (!isset($visibilityCounts[$visibility])) {
                $visibilityCounts[$visibility] = 0;
            }
            $visibilityCounts[$visibility]++;
        }
        
        return $this->render('group/dashboard.html.twig', [
            'groups' => $groups,
            'group_count' => $groupCount,
            'category_counts' => $categoryCounts,
            'visibility_counts' => $visibilityCounts,
            'page_title' => 'Groups Dashboard',
            'search' => '',
            ...$this->getSystemInfo()
        ]);
    }

 /**************************************** Admin *************************************/
    #[Route('/', name: 'app_group_index', methods: ['GET'])]
    public function index(Request $request, GroupRepository $groupRepository): Response
    {
        $search = $request->query->get('search', '');
        
        if (!empty($search)) {
            // Simple search implementation - in a real app, you'd want to use a more sophisticated search
            $groups = $groupRepository->createQueryBuilder('g')
                ->where('g.name LIKE :search OR g.category LIKE :search OR g.city LIKE :search')
                ->setParameter('search', '%' . $search . '%')
                ->getQuery()
                ->getResult();
        } else {
            $groups = $groupRepository->findAll();
        }
        
        return $this->render('group/index.html.twig', [
            'groups' => $groups,
            'search' => $search,
            ...$this->getSystemInfo()
        ]);
    }

    #[Route('/new', name: 'app_group_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $group = new Group();
        $group->setCreatedByUser($this->getUser());
        
        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($group);
            $entityManager->flush();

            $this->addFlash('success', 'Group created successfully!');
            return $this->redirectToRoute('app_group_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('group/new.html.twig', [
            'group' => $group,
            'form' => $form,
            ...$this->getSystemInfo()
        ]);
    }

    #[Route('/{id}', name: 'app_group_show', methods: ['GET'])]
    public function show(GroupRepository $groupRepository, int $id): Response
    {
        $group = $groupRepository->find($id);
        
        if (!$group) {
            throw $this->createNotFoundException('Group not found');
        }

        return $this->render('group/show.html.twig', [
            'group' => $group,
            ...$this->getSystemInfo()
        ]);
    }

    #[Route('/{id}/edit', name: 'app_group_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, GroupRepository $groupRepository, int $id, EntityManagerInterface $entityManager): Response
    {
        $group = $groupRepository->find($id);
        
        if (!$group) {
            throw $this->createNotFoundException('Group not found');
        }

        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Group updated successfully!');

            return $this->redirectToRoute('app_group_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('group/edit.html.twig', [
            'group' => $group,
            'form' => $form,
            ...$this->getSystemInfo()
        ]);
    }

    #[Route('/{id}', name: 'app_group_delete', methods: ['POST'])]
    public function delete(Request $request, GroupRepository $groupRepository, int $id, EntityManagerInterface $entityManager): Response
    {
        $group = $groupRepository->find($id);
        
        if (!$group) {
            throw $this->createNotFoundException('Group not found');
        }

        if ($this->isCsrfTokenValid('delete'.$group->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($group);
            $entityManager->flush();
            
            $this->addFlash('success', 'Group deleted successfully!');
        }

        return $this->redirectToRoute('app_group_index', [], Response::HTTP_SEE_OTHER);
    }

 /**************************************** User *************************************/
    #[Route('/user', name: 'app_group_index_user', methods: ['GET'])]
    public function indexUser(GroupRepository $groupRepository): Response
    {
        return $this->render('group/index_user.html.twig', [
            'groups' => $groupRepository->findAll(),
            ...$this->getSystemInfo()
        ]);
    }

    #[Route('/user/{id}', name: 'app_group_show_user', methods: ['GET'])]
    public function showUser(GroupRepository $groupRepository, int $id): Response
    {
        $group = $groupRepository->find($id);
        
        if (!$group) {
            throw $this->createNotFoundException('Group not found');
        }

        return $this->render('group/show_user.html.twig', [
            'group' => $group,
            ...$this->getSystemInfo()
        ]);
    }
}