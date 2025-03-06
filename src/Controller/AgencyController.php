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
use Symfony\Component\HttpFoundation\Response;
use App\Repository\AgencyRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;





#[Route('/agency', name: 'agency_')]
class AgencyController extends AbstractController
{
    

    private function getSystemInfo(): array
    {
        return [
            'current_time' => new \DateTime('now', new \DateTimeZone('UTC')),
            'current_user' => $this->getUser(),
        ];
    }

    private AgencyRepository $agencyRepository;

    // Inject the AgencyRepository in the constructor
    public function __construct(AgencyRepository $agencyRepository)
    {
        $this->agencyRepository = $agencyRepository;
    }

    #[Route('/agencies', name: 'list', methods: ['GET'])]
    public function agenciesPage(Request $request): Response
    {
        $search = $request->query->get('search');
        $sortBy = $request->query->get('sort', 'asc'); // Default to ascending if no sort is provided
    
        // Retrieve agencies based on search
        $agencies = $this->agencyRepository->findBySearch($search);
    
        // If sorting is applied, sort the agencies
        $agencies = $this->agencyRepository->findBySort($agencies, $sortBy);
    
        return $this->render('agency/agencies.html.twig', [
            'title' => 'Our Agencies',
            'agencies' => $agencies,
            'sort' => $sortBy, // Pass the sort direction to the template
            ...$this->getSystemInfo(),
        ]);
    }
    
    

    


    // #[Route('/agenciess', name: 'list', methods: ['GET'])]
    // public function listAgencies(Request $request): Response
    // {
    //     $search = $request->query->get('search');
    //     $agencies = $this->agencyRepository->findBySearch($search);  // Assume a custom repository method to handle search

    //     return $this->render('agency/agencies.html.twig', [
    //         'agencies' => $agencies
    //     ]);
    // }

    #[Route('/collaborate/{id}', name: 'agency_collaborate', methods: ['POST'])]
public function collaborate(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer, int $id): Response
{
    $agency = $entityManager->getRepository(Agency::class)->find($id);
    if (!$agency) {
        throw $this->createNotFoundException('Agency not found');
    }

    // Get form data
    $subject = $request->request->get('subject');
    $message = $request->request->get('message');
    $agencyEmail = $request->request->get('agency_email');

    if (!$subject || !$message) {
        return new Response('Subject and body are required', 400);
    }

    // Create email
    $email = (new Email())
        ->from('louailamsi11@gmail.com') // Change this to your email
        ->to($agencyEmail)
        ->subject($subject)
        ->text($message);

    // Send email
    $mailer->send($email);

    return $this->redirectToRoute('agency_get_agency', ['id' => $agency->getAgencyId()]);
}


    #[Route('/agency/{id}', name: 'get_agency', methods: ['GET'])]
    public function agencyDetailsPage(int $id, EntityManagerInterface $entityManager): Response
    {
        $agency = $entityManager->getRepository(Agency::class)->find($id);
    
        if (!$agency) {
            throw $this->createNotFoundException('Agency not found');
        }
    
        return $this->render('agency/agency_details.html.twig', [
            'agency' => $agency
        ]);
    }



    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('carint/agency.html.twig', [
            'title' => 'Our agency',
        ]);
    }

    // Updating createAgency to handle the email field
    #[Route('/create', name: 'create', methods: ['POST'])]
    public function createAgency(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate required fields
        if (!isset($data['name'], $data['longitude'], $data['latitude'], $data['description'], $data['email'])) {
            return new JsonResponse(['error' => 'Missing required fields'], 400);
        }

        // Validate and convert agency name to uppercase
        $name = strtoupper($data['name']);
        if (strlen($name) < 3 || strlen($name) > 20) {
            return new JsonResponse(['error' => 'Agency name must be between 3 and 20 characters'], 400);
        }

        // Validate address description length (between 5 and 100 characters)
        $description = $data['description'];
        if (strlen($description) < 5 || strlen($description) > 100) {
            return new JsonResponse(['error' => 'Address description must be between 5 and 100 characters'], 400);
        }

        // Validate latitude and longitude as float
        if (!is_numeric($data['latitude']) || !is_numeric($data['longitude'])) {
            return new JsonResponse(['error' => 'Latitude and Longitude must be numeric values'], 400);
        }

        // Create new Address entity
        $address = new Address();
        $address->setLongitude((float) $data['longitude']);
        $address->setLatitude((float) $data['latitude']);
        $address->setDescription($description);

        // Persist Address entity
        $entityManager->persist($address);
        $entityManager->flush();

        // Create new Agency entity
        $agency = new Agency();
        $agency->setName($name);
        $agency->setEmail($data['email']);  // Set the email
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
        $agency = $entityManager->getRepository(Agency::class)->find($id);
        if (!$agency) {
            return new JsonResponse(['error' => 'Agency not found'], 404);
        }

        $service = $entityManager->getRepository(Service::class)->find($service_id);
        if (!$service) {
            return new JsonResponse(['error' => 'Service not found'], 404);
        }

        $agency->setService($service);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Service added to agency successfully'], 200);
    }

    #[Route('/getbyid/{id}', name: 'get_agency_by_id', methods: ['GET'])]
    public function agencyDetails(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $agency = $entityManager->getRepository(Agency::class)->find($id);
        
        if (!$agency) {
            return new JsonResponse(['error' => 'Agency not found'], 404);
        }

        $data = [
            'agency_id' => $agency->getAgencyId(),
            'name' => $agency->getName(),
            'service_id' => $agency->getService() ? $agency->getService()->getId() : null,
            'address' => [
                'longitude' => $agency->getAddress()->getLongitude(),
                'latitude' => $agency->getAddress()->getLatitude(),
                'description' => $agency->getAddress()->getDescription()
            ]
        ];

        return new JsonResponse($data);
    }


    #[Route('/all', name: 'get_all', methods: ['GET'])]
    public function getAllAgencies(EntityManagerInterface $entityManager): JsonResponse
    {
        $agencies = $entityManager->getRepository(Agency::class)->findAll();

        if (empty($agencies)) {
            return new JsonResponse(['error' => 'No agencies found'], 404);
        }

        $data = array_map(function ($agency) {
            return [
                'agency_id' => $agency->getAgencyId(),
                'name' => $agency->getName(),
                'service_id' => $agency->getService() ? $agency->getService()->getId() : null,
                'address' => [
                    'longitude' => $agency->getAddress()->getLongitude(),
                    'latitude' => $agency->getAddress()->getLatitude(),
                    'description' => $agency->getAddress()->getDescription()
                ]
            ];
        }, $agencies);

        return new JsonResponse($data);
    }

    #[Route('/delete/{id}', name: 'delete', methods: ['DELETE'])]
    public function deleteAgency(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $agency = $entityManager->getRepository(Agency::class)->find($id);
        if (!$agency) {
            return new JsonResponse(['error' => 'Agency not found'], 404);
        }

        $entityManager->remove($agency);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Agency deleted successfully'], 200);
    }

    #[Route('/update/{id}', name: 'update', methods: ['PUT'])]
    public function updateAgency(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $agency = $entityManager->getRepository(Agency::class)->find($id);
        if (!$agency) {
            return new JsonResponse(['error' => 'Agency not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $agency->setName($data['name']);
        }
        if (isset($data['longitude'], $data['latitude'], $data['description'])) {
            $address = $agency->getAddress();
            $address->setLongitude($data['longitude']);
            $address->setLatitude($data['latitude']);
            $address->setDescription($data['description']);
        }

        $entityManager->flush();

        return new JsonResponse(['message' => 'Agency updated successfully']);
    }

    #[Route('/address/update/{id}', name: 'update_address', methods: ['PUT'])]
    public function updateAgencyAddress(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $agency = $entityManager->getRepository(Agency::class)->find($id);
        if (!$agency) {
            return new JsonResponse(['error' => 'Agency not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $address = $agency->getAddress();

        if (isset($data['longitude'])) {
            $address->setLongitude($data['longitude']);
        }
        if (isset($data['latitude'])) {
            $address->setLatitude($data['latitude']);
        }
        if (isset($data['description'])) {
            $address->setDescription($data['description']);
        }

        $entityManager->flush();

        return new JsonResponse(['message' => 'Agency address updated successfully']);
    }
}
