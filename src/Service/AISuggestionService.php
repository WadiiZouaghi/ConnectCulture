<?php

namespace App\Service;

use App\Entity\Group;
use App\Entity\Actor;
use App\Repository\GroupRepository;
use Doctrine\ORM\EntityManagerInterface;

class AISuggestionService
{
    private GroupRepository $groupRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(GroupRepository $groupRepository, EntityManagerInterface $entityManager)
    {
        $this->groupRepository = $groupRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Generate AI-powered suggestions for a user.
     *
     * @param Actor|null $user The user to generate suggestions for (null if not logged in).
     * @return array An array of suggestions (event recommendations and suggested actions).
     */
    public function generateSuggestions(?Actor $user): array
    {
        $suggestions = [
            'events' => [],
            'actions' => [],
        ];

        // 1. Event Recommendations
        // - If user is logged in and has a location, prioritize events in their location.
        // - Otherwise, suggest popular events (based on number of participants).
        $userLocation = $user ? $user->getLocation() : null;
        if ($userLocation) {
            $locationEvents = $this->groupRepository->findBy(
                ['location' => $userLocation],
                ['eventDate' => 'ASC'],
                2 // Limit to 2 events
            );
            foreach ($locationEvents as $event) {
                $suggestions['events'][] = [
                    'text' => "Join \"" . $event->getName() . "\" in " . $event->getLocation(),
                    'event_id' => $event->getId(),
                ];
            }
        }

        // Fallback: Suggest popular events if no location-specific events are found
        if (empty($suggestions['events'])) {
            $popularEvents = $this->groupRepository->findBy(
                [],
                ['maxParticipants' => 'DESC'],
                2 // Limit to 2 events
            );
            foreach ($popularEvents as $event) {
                $suggestions['events'][] = [
                    'text' => "Join \"" . $event->getName() . "\" in " . $event->getLocation(),
                    'event_id' => $event->getId(),
                ];
            }
        }

        // 2. Suggested Actions
        if ($user) {
            // Check if the user has joined any events
            $joinedEvents = $this->groupRepository->findByUserParticipation($user);
            if (!empty($joinedEvents)) {
                $event = $joinedEvents[0]; // Take the first event
                $suggestions['actions'][] = [
                    'text' => "Invite friends to \"" . $event->getName() . "\"",
                    'event_id' => $event->getId(),
                    'action' => 'invite',
                ];
            } else {
                // If no joined events, suggest joining a popular event
                $popularEvent = $this->groupRepository->findOneBy([], ['maxParticipants' => 'DESC']);
                if ($popularEvent) {
                    $suggestions['actions'][] = [
                        'text' => "Explore \"" . $popularEvent->getName() . "\" nearby",
                        'event_id' => $popularEvent->getId(),
                        'action' => 'explore',
                    ];
                }
            }
        } else {
            // If user is not logged in, suggest signing up
            $suggestions['actions'][] = [
                'text' => "Sign up to get personalized event suggestions",
                'action' => 'signup',
            ];
        }

        return $suggestions;
    }
}