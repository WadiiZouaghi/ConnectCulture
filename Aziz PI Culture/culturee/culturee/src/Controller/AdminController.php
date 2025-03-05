<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Repository\EventRepository;
use App\Repository\ParticipationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function dashboard(
        UserRepository $userRepository,
        EventRepository $eventRepository,
        ParticipationRepository $participationRepository
    ): Response {
        // Dashboard Statistics
        $stats = [
            'total_users' => $userRepository->count([]),
            'total_organizers' => $userRepository->count(['roles' => ['ROLE_ORGANIZER']]),
            'total_events' => $eventRepository->count([]),
            'total_participations' => $participationRepository->count([]),
            'banned_users' => $userRepository->count(['banned' => true])
        ];

        // Role stats for the doughnut chart
        $roleStats = [
            'users' => $userRepository->count(['roles' => ['ROLE_USER']]),
            'organizers' => $userRepository->count(['roles' => ['ROLE_ORGANIZER']]),
            'admins' => $userRepository->count(['roles' => ['ROLE_ADMIN']])
        ];

        // Get latest users
        $latestUsers = $userRepository->findBy(
            [], 
            ['createdAt' => 'DESC'],
            5
        );

        // Get banned users
        $bannedUsers = $userRepository->findBy(
            ['banned' => true],
            ['bannedAt' => 'DESC'],
            5
        );

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
            'role_stats' => $roleStats,
            'latest_users' => $latestUsers,
            'banned_users' => $bannedUsers,
            'current_username' => $this->getUser()->getUserIdentifier(),
            'current_date' => (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s')
        ]);
    }

    #[Route('/admin/users', name: 'admin_users_list')]
    public function usersList(Request $request, UserRepository $userRepository): Response
    {
        $search = $request->query->get('search');
        $users = $search 
            ? $userRepository->findBySearch($search) 
            : $userRepository->findAll();

        return $this->render('admin/admin.html.twig', [
            'users' => $users,
            'search' => $search,
            'user_count' => count($users),
            'current_date' => (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s'),
            'current_username' => $this->getUser()->getUserIdentifier()
        ]);
    }

    #[Route('/admin/users/search', name: 'admin_users_search', methods: ['GET'])]
    public function search(Request $request, UserRepository $userRepository): JsonResponse
    {
        try {
            $searchTerm = $request->query->get('term', '');
            
            // Get users from repository
            $users = $userRepository->searchUsers($searchTerm);
            
            // Transform users to array with explicit null checks
            $usersArray = array_map(function($user) {
                return [
                    'id' => $user->getId(),
                    'fullName' => $user->getFullName() ?? '',
                    'email' => $user->getEmail() ?? '',
                    'phone' => $user->getPhone() ?? '',
                    'roles' => $user->getRoles() ?? ['ROLE_USER'],
                    'banned' => $user->isBanned() ?? false,
                ];
            }, $users);

            // Return success response with data
            return new JsonResponse([
                'success' => true,
                'users' => $usersArray,
                'count' => count($usersArray),
                'currentDate' => (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s'),
                'currentUser' => $this->getUser() ? $this->getUser()->getUserIdentifier() : 'Anonymous',
                'searchTerm' => $searchTerm
            ]);

        } catch (\Exception $e) {
            // Log the error
            error_log($e->getMessage());
            
            // Return error response
            return new JsonResponse([
                'success' => false,
                'error' => 'An error occurred while searching.',
                'debug' => $e->getMessage(), // Remove this in production
                'searchTerm' => $searchTerm
            ], 500);
        }
    }
    #[Route('/admin/user/create', name: 'admin_user_create')]
    public function createUser(Request $request, EntityManagerInterface $em): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($user);
            $em->flush();
            
            $this->addFlash('success', 'User created successfully.');
            return $this->redirectToRoute('admin_users_list');
        }

        return $this->render('admin/user_form.html.twig', [
            'form' => $form->createView(),
            'page_title' => 'Create User',
            'current_date' => (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s'),
            'current_username' => $this->getUser()->getUserIdentifier()
        ]);
    }

    #[Route('/admin/user/edit/{id}', name: 'admin_user_edit')]
    public function editUser(User $user, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            
            $this->addFlash('success', 'User updated successfully.');
            return $this->redirectToRoute('admin_users_list');
        }

        return $this->render('admin/user_form.html.twig', [
            'form' => $form->createView(),
            'page_title' => 'Edit User',
            'current_date' => (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s'),
            'current_username' => $this->getUser()->getUserIdentifier()
        ]);
    }

    #[Route('/admin/user/delete/{id}', name: 'admin_user_delete', methods: ['POST'])]
    public function deleteUser(User $user, EntityManagerInterface $em): Response
    {
        try {
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error deleting user: ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_users_list');
    }

    #[Route('/admin/user/{id}/ban', name: 'admin_user_ban', methods: ['POST'])]
    public function banUser(User $user, Request $request, EntityManagerInterface $em): Response
    {
        try {
            $banReason = $request->request->get('ban_reason');
            $user->setBanned(true);
            $user->setBanReason($banReason);
            $user->setBannedAt(new \DateTime());
            
            $em->flush();
            
            return new JsonResponse([
                'success' => true,
                'message' => 'User banned successfully'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/admin/user/{id}/unban', name: 'admin_user_unban', methods: ['GET', 'POST'])]
    public function unbanUser(User $user, EntityManagerInterface $em): Response
    {
        try {
            $user->setBanned(false);
            $user->setBanReason(null);
            $user->setBannedAt(null);
            
            $em->flush();
            
            return new JsonResponse([
                'success' => true,
                'message' => 'User unbanned successfully'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}