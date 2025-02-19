<?php

namespace App\Controller;

use App\Entity\Agency;
use App\Entity\Address;
use App\Entity\Service;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/agency', name: 'agency_')]
class AgencyController extends AbstractController
{
    #[Route('/create', name: 'create', methods: ['POST'])]
    public function createAgency(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate required fields
        if (!isset($data['name'], $data['longitude'], $data['latitude'], $data['description'])) {
            return new JsonResponse(['error' => 'Missing required fields'], 400);
        }

        // Create new Address entity
        $address = new Address();
        $address->setLongitude($data['longitude']);
        $address->setLatitude($data['latitude']);
        $address->setDescription($data['description']);

        // Persist Address entity
        $entityManager->persist($address);
        $entityManager->flush();

        // Create new Agency entity (without a service)
        $agency = new Agency();
        $agency->setName($data['name']);
        $agency->setAddress($address);

        // Persist Agency entity
        $entityManager->persist($agency);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Agency created successfully',
            'agency_id' => $agency->getAgencyId()
        ], 201);
    }

    #[Route('/{id}/add-service/{service_id}', name: 'add_service', methods: ['PUT'])]
    public function addServiceToAgency(int $id, int $service_id, EntityManagerInterface $entityManager): JsonResponse
    {
        // Find Agency
        $agency = $entityManager->getRepository(Agency::class)->find($id);
        if (!$agency) {
            return new JsonResponse(['error' => 'Agency not found'], 404);
        }

        // Find Service
        $service = $entityManager->getRepository(Service::class)->find($service_id);
        if (!$service) {
            return new JsonResponse(['error' => 'Service not found'], 404);
        }

        // Assign Service to Agency
        $agency->setService($service);

        // Save changes
        $entityManager->flush();

        return new JsonResponse(['message' => 'Service added to agency successfully'], 200);
    }

    #[Route('/{id}', name: 'get_agency', methods: ['GET'])]
    public function getAgency(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $agency = $entityManager->getRepository(Agency::class)->find($id);
        if (!$agency) {
            return new JsonResponse(['error' => 'Agency not found'], 404);
        }

        return new JsonResponse([
            'agency_id' => $agency->getAgencyId(),
            'name' => $agency->getName(),
            'service_id' => $agency->getService() ? $agency->getService()->getId() : null,
            'address' => [
                'longitude' => $agency->getAddress()->getLongitude(),
                'latitude' => $agency->getAddress()->getLatitude(),
                'description' => $agency->getAddress()->getDescription()
            ]
        ]);
    }
}
