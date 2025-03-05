<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\LoginAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $userPasswordHasher, 
        UserAuthenticatorInterface $userAuthenticator,
        LoginAuthenticator $authenticator,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Check if email exists
                $existingUser = $entityManager->getRepository(User::class)->findOneBy([
                    'email' => $form->get('email')->getData()
                ]);

                if ($existingUser) {
                    if ($existingUser->isBanned()) {
                        $this->addFlash('error', 'This email address has been banned from registration.');
                    } else {
                        $this->addFlash('error', 'An account with this email already exists.');
                    }
                    return $this->redirectToRoute('app_register');
                }

                // Encode the password
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );

                // Set default values
                $user->setGoogleId(null);
                $user->setBanned(false);
                $user->setRoles(['ROLE_USER']);

                // Persist the new user
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Registration successful! Please log in.');
                return $this->redirectToRoute('app_login');

            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred during registration. Please try again.');
                return $this->redirectToRoute('app_register');
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/register/organizer', name: 'app_register_organizer')]
    public function registerOrganizer(
        Request $request, 
        UserPasswordHasherInterface $userPasswordHasher, 
        UserAuthenticatorInterface $userAuthenticator,
        LoginAuthenticator $authenticator,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Check if email exists
                $existingUser = $entityManager->getRepository(User::class)->findOneBy([
                    'email' => $form->get('email')->getData()
                ]);

                if ($existingUser) {
                    if ($existingUser->isBanned()) {
                        $this->addFlash('error', 'This email address has been banned from registration.');
                    } else {
                        $this->addFlash('error', 'An account with this email already exists.');
                    }
                    return $this->redirectToRoute('app_register_organizer');
                }

                // Encode the password
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );

                // Set default values
                $user->setGoogleId(null);
                $user->setBanned(false);
                $user->setRoles(['ROLE_ORGANIZER']);

                // Persist the new user
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Registration successful! Please log in.');
                return $this->redirectToRoute('app_login');

            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred during registration. Please try again.');
                return $this->redirectToRoute('app_register_organizer');
            }
        }

        return $this->render('registration/register_organizer.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}