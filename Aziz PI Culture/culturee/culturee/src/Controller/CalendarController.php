<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\Repository\ParticipationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface; // Add this import


class CalendarController extends AbstractController
{
    #[Route('/calendar', name: 'app_calendar')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Check if user is logged in
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'You must be logged in to view the calendar.');
            return $this->redirectToRoute('app_login');
        }

        try {
            // Get events directly from the database
            $query = $entityManager->createQuery(
                'SELECT e, p
                FROM App\Entity\Event e
                JOIN e.participations p
                WHERE p.user = :user
                ORDER BY e.date ASC'
            )->setParameter('user', $user);

            $events = $query->getResult();

            // Format events for FullCalendar
            $calendarEvents = [];
            foreach ($events as $event) {
                $calendarEvents[] = [
                    'id' => $event->getId(),
                    'title' => $event->getName(),
                    'start' => $event->getDate()->format('Y-m-d H:i:s'),
                    'end' => $event->getDate()->format('Y-m-d H:i:s'),
                    'allDay' => false,
                    'backgroundColor' => $this->getEventColor($event->getEventtype()),
                    'borderColor' => $this->getEventColor($event->getEventtype()),
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'location' => $event->getDestination(),
                        'description' => $event->getDescription(),
                        'equipment' => $event->getEquipment(),
                        'nbplaces' => $event->getNbplaces(),
                        'eventtype' => $event->getEventtype()
                    ]
                ];
            }

            // Debug information
            dump($calendarEvents); // Remove this in production

            return $this->render('calendar/user_events.html.twig', [
                'events' => json_encode($calendarEvents),
                'debug_events' => $calendarEvents // For debugging
            ]);

        } catch (\Exception $e) {
            // Log the actual error
            error_log($e->getMessage());
            
            $this->addFlash('error', 'Error loading calendar: ' . $e->getMessage());
            return $this->render('calendar/user_events.html.twig', [
                'events' => json_encode([]),
                'error_message' => $e->getMessage()
            ]);
        }
    }

    private function getEventColor(string $eventType): string
    {
        return match (strtolower($eventType)) {
            'cultural' => '#4CAF50',
            'sport' => '#2196F3',
            'music' => '#9C27B0',
            'art' => '#FF9800',
            'theater' => '#E91E63',
            'dance' => '#F44336',
            default => '#757575'
        };
    }
}       
