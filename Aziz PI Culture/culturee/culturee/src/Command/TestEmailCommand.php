<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class TestEmailCommand extends Command
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        parent::__construct();
        $this->mailer = $mailer;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:test-email')
            ->setDescription('Test email configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $email = (new Email())
                ->from(new Address('zoghlamirim116@gmail.com', 'Culture Connect'))
                ->to(new Address('zoghlamirim116@gmail.com'))
                ->subject('Test Email from Culture Connect')
                ->text('This is a test email sent at ' . date('Y-m-d H:i:s'))
                ->html('
                    <h1>Test Email from Culture Connect</h1>
                    <p>This is a test email sent at: ' . date('Y-m-d H:i:s') . '</p>
                    <p>If you received this email, your email configuration is working correctly!</p>
                ');

            $this->mailer->send($email);

            $io->success('Email sent successfully! Check your inbox (and spam folder).');
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error([
                'Failed to send email',
                'Error: ' . $e->getMessage()
            ]);
            
            return Command::FAILURE;
        }
    }
}