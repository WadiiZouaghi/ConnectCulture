<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ResetPasswordRequestType;
use App\Form\ResetPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Psr\Log\LoggerInterface;

class ResetPasswordController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private TokenGeneratorInterface $tokenGenerator,
        private LoggerInterface $logger
    ) {
    }

    #[Route('/reset-password-request', name: 'app_forgot_password_request')]
    public function request(Request $request): Response
    {
        $form = $this->createForm(ResetPasswordRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->processSendingPasswordResetEmail(
                $form->get('email')->getData()
            );
        }

        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    private function processSendingPasswordResetEmail(string $emailFormData): Response
    {
        $this->logger->info('Password reset requested', ['email' => $emailFormData]);

        try {
            $user = $this->entityManager->getRepository(User::class)->findOneBy([
                'email' => $emailFormData,
            ]);

            if (!$user) {
                $this->logger->info('No user found with email', ['email' => $emailFormData]);
                $this->addFlash('reset_password_error', 'If an account exists with this email, a password reset link will be sent.');
                return $this->redirectToRoute('app_forgot_password_request');
            }

            $resetToken = $this->tokenGenerator->generateToken();
            $user->setResetToken($resetToken);
            
            // Set token expiration to 1 hour from now
            $expiration = new \DateTime('+1 hour');
            $user->setResetTokenExpiresAt($expiration);
            
            $this->entityManager->flush();

            $resetUrl = $this->generateUrl(
                'app_reset_password',
                ['token' => $resetToken],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $email = (new Email())
                ->from(new Address('zoghlamirim116@gmail.com', 'Culture Connect'))
                ->to(new Address($user->getEmail(), $user->getFullName()))
                ->subject('Culture Connect - Reset Your Password')
                ->html($this->renderView('reset_password/email.html.twig', [
                    'resetUrl' => $resetUrl,
                    'user' => $user,
                    'expiryTime' => '1 hour'
                ]));

            $this->mailer->send($email);

            $this->logger->info('Password reset email sent successfully', [
                'user_email' => $user->getEmail()
            ]);

            $this->addFlash('success', 'If an account exists with this email, a password reset link has been sent.');
            return $this->redirectToRoute('app_login');

        } catch (\Exception $e) {
            $this->logger->error('Error in password reset process', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->addFlash('reset_password_error', 'An error occurred. Please try again later.');
            return $this->redirectToRoute('app_forgot_password_request');
        }
    }

    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function reset(
        string $token,
        Request $request,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        try {
            $user = $this->entityManager->getRepository(User::class)->findOneBy([
                'resetToken' => $token
            ]);

            if (!$user) {
                $this->addFlash('reset_password_error', 'Invalid reset password link.');
                return $this->redirectToRoute('app_forgot_password_request');
            }

            // Check if token is expired
            if ($user->getResetTokenExpiresAt() < new \DateTime()) {
                $user->setResetToken(null);
                $user->setResetTokenExpiresAt(null);
                $this->entityManager->flush();
                
                $this->addFlash('reset_password_error', 'Your password reset link has expired. Please request a new one.');
                return $this->redirectToRoute('app_forgot_password_request');
            }

            $form = $this->createForm(ResetPasswordType::class);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // Remove reset token and expiration
                $user->setResetToken(null);
                $user->setResetTokenExpiresAt(null);
                
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );

                $this->entityManager->flush();

                // Send confirmation email
                $email = (new Email())
                    ->from(new Address('zoghlamirim116@gmail.com', 'Culture Connect'))
                    ->to(new Address($user->getEmail(), $user->getFullName()))
                    ->subject('Culture Connect - Password Successfully Reset')
                    ->html($this->renderView('reset_password/confirmation_email.html.twig', [
                        'user' => $user
                    ]));

                $this->mailer->send($email);

                $this->addFlash('success', 'Your password has been reset successfully. You can now log in with your new password.');
                return $this->redirectToRoute('app_login');
            }

            return $this->render('reset_password/reset.html.twig', [
                'resetForm' => $form->createView(),
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error in password reset process', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->addFlash('reset_password_error', 'An unexpected error occurred. Please try again.');
            return $this->redirectToRoute('app_forgot_password_request');
        }
    }
}