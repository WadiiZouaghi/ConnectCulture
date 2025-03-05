<?php
namespace App\Controller;

use App\Entity\Event;
use App\Entity\Participation;
use App\Repository\EventRepository;
use App\Repository\ParticipationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Flasher\Prime\FlasherInterface;

#[Route('/participation')]
class ParticipationController extends AbstractController
{
    private function getSystemInfo(): array
    {
        return [
            'current_time' => (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s'),
            'current_user' => $this->getUser()->getUserIdentifier()
        ];
    }

    #[Route('/confirm/{participationId}', name: 'confirm_participation', methods: ['POST'])]
    public function confirmParticipation(
        int $participationId, 
        ParticipationRepository $participationRepository, 
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        FlasherInterface $flasher
    ): Response {
        $participation = $participationRepository->find($participationId);

        if (!$participation) {
            throw $this->createNotFoundException('Participation not found');
        }

        $participation->setStatus(true);
        
        try {
            // Get system info for email
            $systemInfo = $this->getSystemInfo();
            
            // Get user email and event details
            $userEmail = $participation->getUser()->getEmail();
            $eventName = $participation->getEvent()->getName();
            
            // Create email with system info format
            $email = (new Email())
                ->from('zoghlamirim116@gmail.com')
                ->to($userEmail)
                ->subject('Event Participation Confirmed!')
                ->text(
                    "Dear {$participation->getUser()->getUserIdentifier()},\n\n" .
                    "We are pleased to inform you that your participation in the event has been confirmed.\n\n" .
                    "Event Details:\n" .
                    "- Event Name: $eventName\n" .
                    "- Date: " . $participation->getEvent()->getDate()->format('Y-m-d H:i:s') . "\n" .
                    "- Location: " . $participation->getEvent()->getDestination() . "\n\n" .
                    "System Information:\n" .
                    "Current Date and Time (UTC - YYYY-MM-DD HH:MM:SS formatted): {$systemInfo['current_time']}\n" .
                    "Current User's Login: {$systemInfo['current_user']}\n\n" .
                    "Thank you for your participation!\n" .
                    "Best regards,\n" .
                    "The Event Team"
                );

            // Send email
            $transport = Transport::fromDsn('smtp://zoghlamirim116@gmail.com:glyuacroqwbixhjo@smtp.gmail.com:587');
            $mailer = new Mailer($transport);
            $mailer->send($email);

            $entityManager->flush();
            
            $flasher->addSuccess('Participation confirmed and confirmation email sent!');
        } catch (\Exception $e) {
            $flasher->addError('Participation confirmed but failed to send email: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_event_show_organizer', [
            'id' => $participation->getEvent()->getId()
        ]);
    }

    // Your existing participate method
    #[Route('/participate/{eventId}', name: 'participate', methods: ['POST'])]
    public function participate(
        int $eventId, 
        EventRepository $eventRepository, 
        ParticipationRepository $participationRepository, 
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        $event = $eventRepository->find($eventId);

        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }

        // Check if user already participated
        $existingParticipation = $participationRepository->findOneBy([
            'user' => $user, 
            'event' => $event
        ]);
        
        if ($existingParticipation) {
            $this->addFlash('error', 'You have already participated in this event.');
            return $this->redirectToRoute('app_event_show_user', ['id' => $eventId]);
        }

        // Create a new participation
        $participation = new Participation();
        $participation->setUser($user);
        $participation->setEvent($event);
        $participation->setPartdate(new \DateTime());
        $participation->setStatus(false);
        
        $entityManager->persist($participation);

        // Decrement the number of places
        $event->decrementNbplaces();
        $entityManager->flush();

        $this->addFlash('success', 'You have successfully participated. Waiting for organizer confirmation.');

        return $this->redirectToRoute('app_event_show_user', ['id' => $eventId]);
    }

    #[Route('/decline/{participationId}', name: 'decline_participation', methods: ['POST'])]
    public function declineParticipation(int $participationId, ParticipationRepository $participationRepository, EntityManagerInterface $entityManager): Response
    {
        $participation = $participationRepository->find($participationId);

        if (!$participation) {
            throw $this->createNotFoundException('Participation not found');
        }

        $event = $participation->getEvent();

        // Remove the participation
        $entityManager->remove($participation);
        $entityManager->flush();

        // Increment the number of places
        $event->incrementNbplaces();
        $entityManager->flush();

        $this->addFlash('success', 'Participation declined.');

        return $this->redirectToRoute('app_event_show_organizer', ['id' => $event->getId()]);
    }

    #[Route('/cancel/{participationId}', name: 'cancel_participation', methods: ['POST'])]
    public function cancelParticipation(int $participationId, ParticipationRepository $participationRepository, EntityManagerInterface $entityManager): Response
    {
        $participation = $participationRepository->find($participationId);

        if (!$participation) {
            throw $this->createNotFoundException('Participation not found');
        }

        $event = $participation->getEvent();

        // Remove the participation
        $entityManager->remove($participation);
        $entityManager->flush();

        // Increment the number of places
        $event->incrementNbplaces();
        $entityManager->flush();

        $this->addFlash('success', 'Your participation has been cancelled.');

        return $this->redirectToRoute('app_event_show_user', ['id' => $event->getId()]);
    }
}