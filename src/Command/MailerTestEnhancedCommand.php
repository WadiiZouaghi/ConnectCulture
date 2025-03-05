<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Header\Headers;

#[AsCommand(
    name: 'app:mail-test-enhanced',
    description: 'Enhanced email test with headers and authentication',
)]
class MailerTestEnhancedCommand extends Command
{
    public function __construct(
        private MailerInterface $mailer
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        try {
            $testId = uniqid();
            $timestamp = date('Y-m-d H:i:s');
            
            $email = (new Email())
                ->from(new Address('zoghlamirim116@gmail.com', 'Culture Connect Test'))
                ->to(new Address('zoghlamirim116@gmail.com', 'Test Recipient'))
                ->replyTo(new Address('zoghlamirim116@gmail.com', 'Culture Connect Reply'))
                ->priority(Email::PRIORITY_HIGH)
                ->subject('🔒 Culture Connect Security Test - ' . $testId);

            // Add custom headers using getHeaders()
            $email->getHeaders()
                ->addTextHeader('X-Transport', 'Culture Connect')
                ->addTextHeader('X-Test-ID', $testId);

            // Add both HTML and text versions
            $email
                ->html("
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <meta charset=\"UTF-8\">
                        <style>
                            body { 
                                font-family: Arial, sans-serif; 
                                line-height: 1.6; 
                                color: #333;
                                margin: 0;
                                padding: 0;
                            }
                            .container { 
                                max-width: 600px; 
                                margin: 0 auto; 
                                padding: 20px;
                            }
                            .header {
                                background: #0d6efd;
                                color: white;
                                padding: 20px;
                                text-align: center;
                                border-radius: 5px 5px 0 0;
                            }
                            .content {
                                background: #f8f9fa;
                                padding: 20px;
                                margin: 20px 0;
                                border-radius: 5px;
                                border: 1px solid #dee2e6;
                            }
                            .info-item {
                                background: white;
                                padding: 10px;
                                margin: 10px 0;
                                border-radius: 3px;
                                border: 1px solid #eee;
                            }
                            .footer {
                                text-align: center;
                                font-size: 12px;
                                color: #6c757d;
                                margin-top: 20px;
                            }
                        </style>
                    </head>
                    <body>
                        <div class=\"container\">
                            <div class=\"header\">
                                <h1>Culture Connect Test Email</h1>
                            </div>
                            <div class=\"content\">
                                <div class=\"info-item\">
                                    <p><strong>Test ID:</strong> {$testId}</p>
                                    <p><strong>Time:</strong> {$timestamp}</p>
                                </div>
                                <p>This is a test email to verify the email delivery system.</p>
                                <p>If you're seeing this, the HTML email is working correctly.</p>
                            </div>
                            <div class=\"footer\">
                                <p>This is an automated test email from Culture Connect</p>
                                <p>Sent at: {$timestamp}</p>
                            </div>
                        </div>
                    </body>
                    </html>
                ")
                ->text(<<<TEXT
                Culture Connect Test Email
                ========================

                Test ID: {$testId}
                Time: {$timestamp}

                This is a plain text version of the test email.
                If you're seeing this, the text email is working correctly.

                ---
                Sent by Culture Connect Test System
                TEXT
                );

            $io->note('Sending test email...');
            $this->mailer->send($email);
            
            $io->success([
                'Email sent successfully!',
                'Test ID: ' . $testId,
                'Time: ' . $timestamp,
                '',
                'Please check:',
                '1. Your main inbox',
                '2. Spam folder',
                '3. All Mail folder',
                '4. Try searching for "Culture Connect" or the Test ID',
                '',
                'The email might take a few minutes to arrive.',
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error([
                'Failed to send email',
                'Error: ' . $e->getMessage(),
                'Type: ' . get_class($e),
                'File: ' . $e->getFile(),
                'Line: ' . $e->getLine()
            ]);
            
            return Command::FAILURE;
        }
    }
}