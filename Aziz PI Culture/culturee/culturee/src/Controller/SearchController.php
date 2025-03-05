<?php

namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search')]
    public function search(Request $request, EventRepository $eventRepository): JsonResponse
    {
        try {
            // Get search query and current user
            $searchTerm = $request->query->get('query', '');
            $user = $this->getUser();

            if (!$user) {
                throw new \Exception('User not authenticated');
            }

            // Get events
            $events = $eventRepository->createQueryBuilder('e')
                ->where('e.user = :user')
                ->andWhere('LOWER(e.name) LIKE :searchTerm OR LOWER(e.destination) LIKE :searchTerm')
                ->setParameter('user', $user)
                ->setParameter('searchTerm', '%' . strtolower($searchTerm) . '%')
                ->getQuery()
                ->getResult();

            $results = [];
            foreach ($events as $event) {
                $results[] = [
                    'id' => $event->getId(),
                    'name' => $event->getName(),
                    'destination' => $event->getDestination(),
                    'date' => $event->getDate() ? $event->getDate()->format('Y-m-d H:i:s') : '',
                    'nbplaces' => $event->getNbplaces(),
                    'description' => $event->getDescription() ?: '',
                    'equipment' => $event->getEquipment() ?: '',
                    'eventtype' => $event->getEventtype() ?: '',
                    'image' => $event->getImage() ?: ''
                ];
            }

            return new JsonResponse([
                'success' => true,
                'data' => $results,
                'debug' => [
                    'searchTerm' => $searchTerm,
                    'userEmail' => $user->getUserIdentifier(),
                    'eventCount' => count($results)
                ]
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
                'debug' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }
}