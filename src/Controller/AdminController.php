<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
public function index(Request $request, UserRepository $userRepository): Response
{
    $search = $request->query->get('search');
    $users = $search 
        ? $userRepository->findBySearch($search) 
        : $userRepository->findAll();

    $userCount = count($users);

    return $this->render('admin/admin.html.twig', [
        'page_title' => 'Admin ',
        'users' => $users,
        'search' => $search,
        'user_count' => $userRepository->count([]) // Count all users
    ]);
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
        return $this->redirectToRoute('admin_dashboard');
    }

    return $this->render('admin/user_form.html.twig', [
        'form' => $form->createView(),
        'page_title' => 'Create User'
    ]);
}

    #[Route('/admin/user/edit/{id}', name: 'admin_user_edit')]
    public function editUser(User $user, Request $request, EntityManagerInterface $em): Response
{
    $form = $this->createForm(UserType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->flush();
        return $this->redirectToRoute('admin_dashboard');
    }

    return $this->render('admin/user_form.html.twig', [
        'form' => $form->createView(),
        'page_title' => 'Edit User'
    ]);
}

    #[Route('/admin/user/delete/{id}', name: 'admin_user_delete', methods: ['POST'])]
    public function deleteUser(User $user, EntityManagerInterface $em): Response
    {
        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute('admin_dashboard');
    }
}
