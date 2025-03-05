<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/admin')]
class BanController extends AbstractController
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/user/{id}/ban', name: 'admin_user_ban', methods: ['POST'])]
    public function banUser(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // Check if user has admin privileges
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->logger->warning('Unauthorized ban attempt', [
                'attempted_by' => $this->getUser()->getUserIdentifier(),
                'target_user' => $user->getEmail()
            ]);
            throw new AccessDeniedException('Only administrators can ban users.');
        }

        // Prevent self-banning
        if ($user === $this->getUser()) {
            $this->addFlash('error', 'You cannot ban yourself.');
            return $this->redirectToRoute('admin_users_list');
        }

        // Check if user is already banned
        if ($user->isBanned()) {
            $this->addFlash('error', sprintf('User %s is already banned.', $user->getEmail()));
            return $this->redirectToRoute('admin_users_list');
        }

        $reason = $request->request->get('ban_reason');
        
        if (empty($reason)) {
            $this->addFlash('error', 'A ban reason is required.');
            return $this->redirectToRoute('admin_users_list');
        }
        
        try {
            // Set ban status and reason
            $user->setBanned(true);
            $user->setBanReason($reason);
            
            // Log the action
            $this->logger->info('User banned', [
                'banned_user' => $user->getEmail(),
                'banned_by' => $this->getUser()->getUserIdentifier(),
                'reason' => $reason,
                'timestamp' => new \DateTime('now', new \DateTimeZone('UTC'))
            ]);

            $entityManager->flush();

            $this->addFlash('success', sprintf('User %s has been banned.', $user->getEmail()));
        } catch (\Exception $e) {
            $this->logger->error('Error banning user', [
                'error' => $e->getMessage(),
                'user' => $user->getEmail()
            ]);
            $this->addFlash('error', 'An error occurred while banning the user.');
        }

        return $this->redirectToRoute('admin_users_list');
    }

    #[Route('/user/{id}/unban', name: 'admin_user_unban', methods: ['POST'])]
    public function unbanUser(
        User $user,
        EntityManagerInterface $entityManager
    ): Response {
        // Check if user has admin privileges
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->logger->warning('Unauthorized unban attempt', [
                'attempted_by' => $this->getUser()->getUserIdentifier(),
                'target_user' => $user->getEmail()
            ]);
            throw new AccessDeniedException('Only administrators can unban users.');
        }

        // Check if user is actually banned
        if (!$user->isBanned()) {
            $this->addFlash('error', sprintf('User %s is not banned.', $user->getEmail()));
            return $this->redirectToRoute('admin_users_list');
        }

        try {
            // Remove ban status and reason
            $user->setBanned(false);
            $user->setBanReason(null);
            
            // Log the action
            $this->logger->info('User unbanned', [
                'unbanned_user' => $user->getEmail(),
                'unbanned_by' => $this->getUser()->getUserIdentifier(),
                'timestamp' => new \DateTime('now', new \DateTimeZone('UTC'))
            ]);

            $entityManager->flush();

            $this->addFlash('success', sprintf('User %s has been unbanned.', $user->getEmail()));
        } catch (\Exception $e) {
            $this->logger->error('Error unbanning user', [
                'error' => $e->getMessage(),
                'user' => $user->getEmail()
            ]);
            $this->addFlash('error', 'An error occurred while unbanning the user.');
        }

        return $this->redirectToRoute('admin_users_list');
    }

    #[Route('/user/{id}/ban-status', name: 'admin_user_ban_status', methods: ['GET'])]
    public function getBanStatus(User $user): Response
    {
        // Check if user has admin privileges
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Only administrators can view ban status.');
        }

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'isBanned' => $user->isBanned(),
            'banReason' => $user->getBanReason(),
            'bannedAt' => $user->getBannedAt() ? $user->getBannedAt()->format('Y-m-d H:i:s') : null
        ]);
    }
}