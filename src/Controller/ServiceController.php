<?php

namespace App\Controller;

use App\Entity\Service;
use App\Form\ServiceType;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface; // Import EntityManagerInterface
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ServiceController extends AbstractController
{
    private $entityManager;

    // Inject EntityManagerInterface in the constructor
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/service", name="service_index", methods={"GET"})
     */
    public function index(ServiceRepository $serviceRepository): Response
    {
        // Fetch all services from the repository
        $services = $serviceRepository->findAll();

        return $this->render('service/index.html.twig', [
            'services' => $services,
        ]);
    }

    /**
     * @Route("/service/new", name="service_new", methods={"GET", "POST"})
     */
    public function new(Request $request): Response
    {
        $service = new Service();
        $form = $this->createForm(ServiceType::class, $service);

        // Handle the form submission
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Persist the new service to the database using the injected entity manager
            $this->entityManager->persist($service);
            $this->entityManager->flush();

            // Redirect to the service list page after saving
            return $this->redirectToRoute('service_index');
        }

        return $this->render('service/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/service/{id}", name="service_show", methods={"GET"})
     */
    public function show(Service $service): Response
    {
        return $this->render('service/show.html.twig', [
            'service' => $service,
        ]);
    }

    /**
     * @Route("/service/{id}/edit", name="service_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Service $service): Response
    {
        $form = $this->createForm(ServiceType::class, $service);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Update the service in the database using the injected entity manager
            $this->entityManager->flush();

            // Redirect to the service list page after editing
            return $this->redirectToRoute('service_index');
        }

        return $this->render('service/edit.html.twig', [
            'service' => $service,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/service/{id}/delete", name="service_delete", methods={"POST"})
     */
    public function delete(Request $request, Service $service): Response
    {
        if ($this->isCsrfTokenValid('delete' . $service->getId(), $request->request->get('_token'))) {
            // Remove the service from the database using the injected entity manager
            $this->entityManager->remove($service);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('service_index');
    }
}
