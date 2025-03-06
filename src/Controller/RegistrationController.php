<?php

namespace App\Controller;

use App\Entity\Actor;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/signup', name: 'app_signup')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $actor = new Actor();
        $form = $this->createForm(RegistrationFormType::class, $actor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash the password
            $hashedPassword = $passwordHasher->hashPassword($actor, $actor->getPassword());
            $actor->setPassword($hashedPassword);
            $actor->setRoles(['ROLE_USER']);

            $entityManager->persist($actor);
            $entityManager->flush();

            $this->addFlash('success', 'Registration successful! Please log in.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/signup.html.twig', [
            'registration_form' => $form->createView(),
        ]);
    }
}