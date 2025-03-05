<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

class MailerDiagnosticController extends AbstractController
{
    #[Route('/test-mail/{type}', name: 'app_test_mail')]
    public function testMail(MailerInterface $mailer, string $type = 'simple'): Response
    {
        try {
            $testId = date('Ymd_His_') . substr(uniqid(), -6);
            
            $email = (new Email())
                ->from(new Address('zoghlamirim116@gmail.com', 'Culture Connect'))
                ->to(new Address('zoghlamirim116@gmail.com', 'Test Recipient'))
                ->subject('Culture Connect Test ' . $testId)
                ->text("This is a test email ($type) sent at " . date('Y-m-d H:i:s'));

            if ($type === 'html') {
                $email->html(<<<HTML
                    <div style="font-family: Arial, sans-serif; padding: 20px;">
                        <h1>Test Email</h1>
                        <p>Test ID: $testId</p>
                        <p>Time: {$this->getCurrentTime()}</p>
                    </div>
                HTML);
            }

            $mailer->send($email);

            return new Response(<<<HTML
                <html>
                <body>
                    <h1>Email Test Results</h1>
                    <p style="color: green;">Email sent successfully!</p>
                    <p>Test ID: $testId</p>
                    <p>Time: {$this->getCurrentTime()}</p>
                    <p>Please check:</p>
                    <ul>
                        <li>Your inbox</li>
                        <li>Spam folder</li>
                        <li>"All Mail" folder (in Gmail)</li>
                    </ul>
                    <p><small>Note: It might take a few minutes for the email to arrive.</small></p>
                </body>
                </html>
            HTML);

        } catch (\Exception $e) {
            return new Response(
                '<h1>Error</h1><pre>' . $e->getMessage() . '</pre>',
                500
            );
        }
    }

    private function getCurrentTime(): string
    {
        return (new \DateTime())->format('Y-m-d H:i:s T');
    }
}