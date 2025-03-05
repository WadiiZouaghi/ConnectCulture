<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

class EmailTestController extends AbstractController
{
    #[Route('/email-test', name: 'app_email_test')]
    public function index(MailerInterface $mailer): Response
    {
        try {
            $testId = uniqid();
            $timestamp = date('Y-m-d H:i:s');
            
            $email = (new Email())
                ->from(new Address('zoghlamirim116@gmail.com', 'Culture Connect Web Test'))
                ->to(new Address('zoghlamirim116@gmail.com', 'Test Recipient'))
                ->replyTo(new Address('zoghlamirim116@gmail.com', 'Culture Connect Reply'))
                ->priority(Email::PRIORITY_HIGH)
                ->subject('🌐 Culture Connect Web Test - ' . $testId)
                ->html($this->renderView('email_test/test_email.html.twig', [
                    'testId' => $testId,
                    'timestamp' => $timestamp
                ]));

            $mailer->send($email);

            return $this->render('email_test/success.html.twig', [
                'testId' => $testId,
                'timestamp' => $timestamp
            ]);

        } catch (\Exception $e) {
            return $this->render('email_test/error.html.twig', [
                'error' => $e->getMessage()
            ]);
        }
    }
}