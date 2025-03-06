<?php

namespace App\Controller;

use App\Entity\Service;
use App\Entity\ServiceEquipment;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ServiceEquipmentType;
use App\Form\ServiceType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;



#[Route('/service', name: 'service_')]
class ServiceController extends AbstractController
{
    #[Route('/new', name: 'app_service_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        if (!$data || empty($data['name']) || empty($data['description'])) {
            return new JsonResponse(['error' => 'Service name and description are required.'], 400);
        }
    
        $service = new Service();
        $service->setName($data['name']);
        $service->setDescription($data['description']); // Ensure description is set
    
        // Handle service equipments if provided
        if (!empty($data['serviceEquipments'])) {
            foreach ($data['serviceEquipments'] as $equipmentData) {
                if (empty($equipmentData['name'])) {
                    return new JsonResponse(['error' => 'Service equipment name is required.'], 400);
                }
    
                $serviceEquipment = new ServiceEquipment();
                $serviceEquipment->setName($equipmentData['name']);
                $serviceEquipment->setDescription($equipmentData['description'] ?? ''); // Default empty description
    
                if (!empty($equipmentData['image'])) {
                    $imageData = base64_decode($equipmentData['image']);
                    if ($imageData === false) {
                        return new JsonResponse(['error' => 'Invalid image format.'], 400);
                    }
                
                    $newFilename = uniqid() . '.png';
                    $imagePath = $this->getParameter('images_directory') . '/' . $newFilename;
                
                    try {
                        file_put_contents($imagePath, $imageData);
                        $serviceEquipment->setImage($newFilename);
                    } catch (\Exception $e) {
                        return new JsonResponse(['error' => 'Error saving the image'], 500);
                    }
                }
                
    
                $service->addServiceEquipment($serviceEquipment);
            }
        }
    
        $entityManager->persist($service);
        $entityManager->flush();
    
        return new JsonResponse([
            'success' => 'Service created successfully!',
            'service' => [
                'id' => $service->getId(),
                'name' => $service->getName(),
                'description' => $service->getDescription(), // Return description
                'serviceEquipments' => array_map(function ($equipment) {
                    return [
                        'name' => $equipment->getName(),
                        'description' => $equipment->getDescription(),
                        'image' => $equipment->getImage()
                    ];
                }, $service->getServiceEquipments()->toArray())
            ]
        ], 201);
    }
    



    #[Route('/{id}/edit', name: 'app_service_edit', methods: ['PUT'])]
    public function edit(Request $request, ServiceRepository $serviceRepository, int $id, EntityManagerInterface $entityManager): Response
    {
        $service = $serviceRepository->find($id);
        if (!$service) {
            throw $this->createNotFoundException('Service not found');
        }

        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Loop through service equipments and handle image upload
            foreach ($service->getServiceEquipments() as $serviceEquipment) {
                /** @var UploadedFile $imageFile */
                $imageFile = $serviceEquipment->getImage();

                if ($imageFile) {
                    $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                    try {
                        // Delete old image if exists
                        $oldImage = $serviceEquipment->getImage();
                        if ($oldImage) {
                            $oldImagePath = $this->getParameter('images_directory') . '/' . $oldImage;
                            if (file_exists($oldImagePath)) {
                                unlink($oldImagePath);
                            }
                        }

                        $imageFile->move(
                            $this->getParameter('images_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Error uploading the image');
                        return $this->redirectToRoute('app_service_edit', ['id' => $service->getId()]);
                    }

                    $serviceEquipment->setImage($newFilename); // Set image on the service equipment
                }
            }

            $entityManager->flush();
            $this->addFlash('success', 'Service updated successfully!');

            return $this->redirectToRoute('app_service_index');
        }

        return $this->render('service/edit.html.twig', [
            'service' => $service,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'app_service_delete', methods: ['DELETE'])]
    public function delete(Request $request, ServiceRepository $serviceRepository, int $id, EntityManagerInterface $entityManager): Response
    {
        $service = $serviceRepository->find($id);

        if (!$service) {
            throw $this->createNotFoundException('Service not found');
        }

        if ($this->isCsrfTokenValid('delete'.$service->getId(), $request->request->get('_token'))) {
            // Delete images for service equipment
            foreach ($service->getServiceEquipments() as $serviceEquipment) {
                $image = $serviceEquipment->getImage();
                if ($image) {
                    $imagePath = $this->getParameter('images_directory') . '/' . $image;
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
            }

            $entityManager->remove($service);
            $entityManager->flush();

            $this->addFlash('success', 'Service deleted successfully!');
        }

        return $this->redirectToRoute('app_service_index');
    }
    #[Route('/all', name: 'app_service_get_all', methods: ['GET'])]
    public function getAll(ServiceRepository $serviceRepository): JsonResponse
    {
        $services = $serviceRepository->findAll();
        return $this->json($services);
    }

    #[Route('/getbyid/{id}', name: 'app_service_get_by_id', methods: ['GET'])]
    public function getById(ServiceRepository $serviceRepository, int $id): JsonResponse
    {
        $service = $serviceRepository->find($id);
        
        if (!$service) {
            return $this->json(['message' => 'Service not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($service);
    }
}
