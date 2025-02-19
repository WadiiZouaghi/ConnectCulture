<?php

namespace App\Controller;

use App\Entity\Service;
use App\Entity\ServiceEquipment;
use App\Entity\Image;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/service', name: 'service_')]
class ServiceController extends AbstractController
{
    #[Route('/create', name: 'create', methods: ['POST'])]
    public function createService(Request $request, EntityManagerInterface $entityManager): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    // Validate required fields
    if (!isset($data['name'], $data['description'], $data['equipments'])) {
        return new JsonResponse(['error' => 'Missing required fields'], 400);
    }

    // Create new Service entity
    $service = new Service();
    $service->setName($data['name']);
    $service->setDescription($data['description']);

    // Persist Service entity
    $entityManager->persist($service);
    $entityManager->flush();

    // Handle Service Equipment
    foreach ($data['equipments'] as $equipmentData) {
        if (!isset($equipmentData['name'], $equipmentData['description'])) {
            return new JsonResponse(['error' => 'Missing fields in equipment'], 400);
        }

        $equipment = new ServiceEquipment();
        $equipment->setName($equipmentData['name']);
        $equipment->setDescription($equipmentData['description']);

        // Handle Equipment Image (One-to-One)
        if (!empty($equipmentData['image'])) {
            $imageData = $equipmentData['image'];
            $image = new Image();
            $image->setName($imageData['name']);
            $image->setImageUrl($imageData['url']);

            $entityManager->persist($image);
            $equipment->setImage($image);  // Associate the image with the equipment
        }

        $entityManager->persist($equipment);
        $service->addServiceEquipment($equipment);  // Add equipment to service
    }

    $entityManager->flush();

    return new JsonResponse([
        'message' => 'Service created successfully',
        'service_id' => $service->getId()
    ], 201);
}


    #[Route('/{id}', name: 'get_service', methods: ['GET'])]
    public function getService(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $service = $entityManager->getRepository(Service::class)->find($id);
        if (!$service) {
            return new JsonResponse(['error' => 'Service not found'], 404);
        }

        $response = [
            'service_id' => $service->getId(),
            'name' => $service->getName(),
            'description' => $service->getDescription(),
            'images' => array_map(fn($img) => ['name' => $img->getName(), 'url' => $img->getImageUrl()], $service->getImages()->toArray()),
            'equipments' => []
        ];

        foreach ($service->getServiceEquipments() as $equipment) {
            $response['equipments'][] = [
                'equipment_id' => $equipment->getId(),
                'name' => $equipment->getName(),
                'description' => $equipment->getDescription(),
                'images' => array_map(fn($img) => ['name' => $img->getName(), 'url' => $img->getImageUrl()], $equipment->getImages()->toArray()),
            ];
        }

        return new JsonResponse($response);
    }
}
