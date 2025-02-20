<?php

namespace App\Controller;

use App\Entity\Service;
use App\Entity\ServiceEquipment;
use App\Entity\Image;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/service', name: 'service_')]
class ServiceController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/service.html.twig', [
            'title' => 'Our Services',
        ]);
    }

    #[Route('/create', name: 'create', methods: ['POST'])]
    public function createService(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name'], $data['description'], $data['equipments'])) {
            return new JsonResponse(['error' => 'Missing required fields'], 400);
        }

        $service = new Service();
        $service->setName($data['name']);
        $service->setDescription($data['description']);

        $entityManager->persist($service);
        
        foreach ($data['equipments'] as $equipmentData) {
            if (!isset($equipmentData['name'], $equipmentData['description'])) {
                return new JsonResponse(['error' => 'Missing fields in equipment'], 400);
            }

            $equipment = new ServiceEquipment();
            $equipment->setName($equipmentData['name']);
            $equipment->setDescription($equipmentData['description']);
            $equipment->setService($service);

            if (!empty($equipmentData['imageName'])) {
                $equipment->setImageName($equipmentData['imageName']);
            }

            $entityManager->persist($equipment);
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
            'equipments' => []
        ];

        foreach ($service->getServiceEquipments() as $equipment) {
            $response['equipments'][] = [
                'equipment_id' => $equipment->getId(),
                'name' => $equipment->getName(),
                'description' => $equipment->getDescription(),
                'imageName' => $equipment->getImageName(),
            ];
        }

        return new JsonResponse($response);
    }
}
