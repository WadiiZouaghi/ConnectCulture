<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Check if the user is already logged in, redirect to the home page or other target.
        if ($this->getUser()) {
            return $this->redirectToRoute('home'); // Adjust 'home' to your desired redirect route
        }

        // Get login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // Get the last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    #[Route('/admin', name: 'app_admin')]
    public function adminDashboard(): Response
    {
        return $this->render('admin/admin.html.twig');
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(UrlGeneratorInterface $urlGenerator): RedirectResponse
    {
        // First, let Symfony handle the logout
        throw new \RuntimeException('You must configure the logout path to be handled by the firewall.');
        
        // This line will never be executed, but shows where it would redirect
        return new RedirectResponse($urlGenerator->generate('app_login'));
    }
}