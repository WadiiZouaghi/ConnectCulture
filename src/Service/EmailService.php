<?php
namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Attribute\Route;


class EmailService
{
    private $mailer;
    private $logger;

    public function __construct(MailerInterface $mailer, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    public function sendEmail()
    {
        try {
            $email = (new Email())
            ->from('hello@example.com')
            ->to('you@example.com')
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun again!')
            ->html('<p>See Twig integration for better HTML integration!</p>');

            $this->mailer->send($email);
            // Log success (useful in production environment)
            $this->logger->info('Email sent successfully to Mailtrap!');

            // Return success message (you can customize this based on the context)
            return 'Email has been sent successfully!';
        } catch (\Exception $e) {
            // Log the error
            $this->logger->error('Error sending email: ' . $e->getMessage());

            // Return the error message
            return 'Error: ' . $e->getMessage();
        }


}
}
