<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-test-user',
    description: 'Creates a test user with admin privileges',
)]
class CreateTestUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Check if user already exists
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'admin2@example.com']);
        
        if ($existingUser) {
            $io->note('Admin user already exists. Updating password...');
            $user = $existingUser;
        } else {
            $io->note('Creating new admin user...');
            $user = new User();
            $user->setFullName('Admin User 2');
            $user->setEmail('admin2@example.com');
            $user->setUsername('admin2');
            $user->setPhone('123456789');
            $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
            
            $this->entityManager->persist($user);
        }
        
        // Set password
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'admin123');
        $user->setPassword($hashedPassword);

        $this->entityManager->flush();

        $io->success('Admin user created/updated successfully!');
        $io->table(
            ['Email', 'Password', 'Roles'],
            [['admin2@example.com', 'admin123', implode(', ', $user->getRoles())]]
        );

        return Command::SUCCESS;
    }
}