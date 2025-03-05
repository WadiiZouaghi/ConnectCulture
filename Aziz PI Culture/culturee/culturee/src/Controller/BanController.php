<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class BanController extends AbstractController
{
    #[Route('/user/ban/{id}', name: 'admin_user_ban')]
    public function banUser(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if ($user === $this->getUser()) {
            $this->addFlash('error', 'You cannot ban yourself.');
            return $this->redirectToRoute('admin_dashboard');
        }

        $reason = $request->request->get('ban_reason');
        
        $user->setBanned(true);
        $user->setBanReason($reason);
        $entityManager->flush();

        $this->addFlash('success', sprintf('User %s has been banned.', $user->getEmail()));
        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/user/unban/{id}', name: 'admin_user_unban')]
    public function unbanUser(
        User $user,
        EntityManagerInterface $entityManager
    ): Response {
        $user->setBanned(false);
        $entityManager->flush();

        $this->addFlash('success', sprintf('User %s has been unbanned.', $user->getEmail()));
        return $this->redirectToRoute('admin_dashboard');
    }
}