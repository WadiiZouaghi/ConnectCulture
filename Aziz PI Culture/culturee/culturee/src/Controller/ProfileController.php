<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profile')]
class ProfileController extends AbstractController
{
/************************************** Admin Profile  *************************************/

    #[Route('/', name: 'app_profile')]
    public function index(): Response
    {
        return $this->render('profile/profile.html.twig', [
            'page_title' => 'Profile',
        ]);
    }

    #[Route('/edit', name: 'app_profile_edit')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser(); // Get the logged-in user

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Profile updated successfully.');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
            'page_title' => 'Edit Profile',
        ]);
    }

/************************************** User Profile  *************************************/

    #[Route('/user', name: 'app_profile_User')]
    public function indexUser(): Response
    {
        return $this->render('profile/profileUser.html.twig', [
            'page_title' => 'Profile',
        ]);
    }

    #[Route('/edit/user', name: 'app_profile_edit_User')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function editUser(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser(); // Get the logged-in user

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Profile updated successfully.');
            return $this->redirectToRoute('app_profile_User');
        }

        return $this->render('profile/editUser.html.twig', [
            'form' => $form->createView(),
            'page_title' => 'Edit Profile',
        ]);
    }

/************************************** Organizer Profile  *************************************/

    #[Route('/organizer', name: 'app_profile_organizer')]
    public function indexOrganizer(): Response
    {
        return $this->render('profile/profileOrg.html.twig', [
            'page_title' => 'Profile',
        ]);
    }

    #[Route('/edit/organizer', name: 'app_profile_edit_organizer')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function editOrganizer(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser(); // Get the logged-in user

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Profile updated successfully.');
            return $this->redirectToRoute('app_profile_organizer');
        }

        return $this->render('profile/editOrg.html.twig', [
            'form' => $form->createView(),
            'page_title' => 'Edit Profile',
        ]);
    }
}