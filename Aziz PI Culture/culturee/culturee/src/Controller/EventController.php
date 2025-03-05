<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;
use Flasher\Prime\FlasherInterface;

#[Route('/event')]
final class EventController extends AbstractController
{
    private function getSystemInfo(): array
    {
        return [
            'current_time' => new \DateTime('now', new \DateTimeZone('UTC')),
            'current_user' => $this->getUser(),
        ];
    }
    // src/Controller/EventController.php

    public function confirmParticipation(
        Participation $participation, 
        FlasherInterface $flasher
    ): Response 
    {
        $participation->setStatus('confirmed');
        $this->entityManager->flush();

        // Add notification
        $flasher->addSuccess('Your participation has been confirmed!');

        return $this->redirectToRoute('app_event_index_User');
    }


/**************************************** Admin *************************************/
    #[Route('/', name: 'app_event_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository): Response
    {
        return $this->render('event/index.html.twig', [
            'events' => $eventRepository->findAll(),
            'current_username' => 'Administrator',
            'current_date' => (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s')
        ]);
    }

    #[Route('/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $event->setUser($this->getUser());
        
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Error uploading the image');
                    return $this->redirectToRoute('app_event_new');
                }

                $event->setImage($newFilename);
            }

            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'Event created successfully!');
            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event/new.html.twig', [
            'event' => $event,
            'form' => $form,
            ...$this->getSystemInfo()
        ]);
    }

    #[Route('/{id}', name: 'app_event_show', methods: ['GET'])]
    public function show(EventRepository $eventRepository, int $id): Response
    {
        $event = $eventRepository->find($id);
        
        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }

        return $this->render('event/show.html.twig', [
            'event' => $event,
            ...$this->getSystemInfo()
        ]);
    }

    #[Route('/{id}/edit', name: 'app_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EventRepository $eventRepository, int $id, EntityManagerInterface $entityManager): Response
    {
        $event = $eventRepository->find($id);
        
        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                try {
                    // Delete old image if exists
                    $oldImage = $event->getImage();
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
                    return $this->redirectToRoute('app_event_edit', ['id' => $event->getId()]);
                }

                $event->setImage($newFilename);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Event updated successfully!');

            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form,
            ...$this->getSystemInfo()
        ]);
    }

    #[Route('/{id}', name: 'app_event_delete', methods: ['POST'])]
    public function delete(Request $request, EventRepository $eventRepository, int $id, EntityManagerInterface $entityManager): Response
    {
        $event = $eventRepository->find($id);
        
        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }

        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            // Delete the image file if it exists
            $image = $event->getImage();
            if ($image) {
                $imagePath = $this->getParameter('images_directory') . '/' . $image;
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $entityManager->remove($event);
            $entityManager->flush();
            
            $this->addFlash('success', 'Event deleted successfully!');
        }

        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }

/**************************************** User *************************************/
    #[Route('/user', name: 'app_event_index_User', methods: ['GET'])]
    public function indexUser(EventRepository $eventRepository): Response
    {
        return $this->render('event/indexUser.html.twig', [
            'events' => $eventRepository->findAll(),
            ...$this->getSystemInfo()
        ]);
    }

    #[Route('/user/{id}', name: 'app_event_show_User', methods: ['GET'])]
    public function showUser(EventRepository $eventRepository, int $id): Response
    {
        $event = $eventRepository->find($id);
        
        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }

        return $this->render('event/showUser.html.twig', [
            'event' => $event,
            ...$this->getSystemInfo()
        ]);
    }

/**************************************** Organizer *************************************/
    #[Route('/organizer', name: 'app_event_index_organizer', methods: ['GET'])]
    public function indexOrganizer(EventRepository $eventRepository): Response
    {
        $currentDate = new \DateTime();

        return $this->render('event/indexOrganizer.html.twig', [
            'events' => $eventRepository->findByUserAndDateGreaterThanEqual($this->getUser(), $currentDate),
            ...$this->getSystemInfo()
        ]);
    }

    #[Route('/organizer/{id}', name: 'app_event_show_organizer', methods: ['GET'])]
    public function showOrganizer(EventRepository $eventRepository, int $id): Response
    {
        $event = $eventRepository->find($id);
        
        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }

        return $this->render('event/showOrganizer.html.twig', [
            'event' => $event,
            ...$this->getSystemInfo()
        ]);
    }

    #[Route('/organizer/new', name: 'app_event_new_organizer', methods: ['GET', 'POST'])]
    public function newOrganizer(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $event->setUser($this->getUser());
        
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Error uploading the image');
                    return $this->redirectToRoute('app_event_new_organizer');
                }

                $event->setImage($newFilename);
            }

            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'Event created successfully!');
            return $this->redirectToRoute('app_event_index_organizer', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event/newOrganizer.html.twig', [
            'event' => $event,
            'form' => $form,
            ...$this->getSystemInfo()
        ]);
    }

    #[Route('/organizer/historique', name: 'app_event_historique_organizer', methods: ['GET'])]
    public function historiqueOrganizer(EventRepository $eventRepository): Response
    {
        $currentDate = new \DateTime();
        $user = $this->getUser();

        return $this->render('event/historique.html.twig', [
            'events' => $eventRepository->findEndedEventsByOrganizer($user, $currentDate),
            ...$this->getSystemInfo()
        ]);
    }

    #[Route('/organizer/{id}/edit', name: 'app_event_edit_organizer', methods: ['GET', 'POST'])]
    public function editOrganizer(Request $request, EventRepository $eventRepository, int $id, EntityManagerInterface $entityManager): Response
    {
        $event = $eventRepository->find($id);
        
        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                try {
                    // Delete old image if exists
                    $oldImage = $event->getImage();
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
                    return $this->redirectToRoute('app_event_edit_organizer', ['id' => $event->getId()]);
                }

                $event->setImage($newFilename);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Event updated successfully!');

            return $this->redirectToRoute('app_event_index_organizer', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event/editOrganizer.html.twig', [
            'event' => $event,
            'form' => $form,
            ...$this->getSystemInfo()
        ]);
    }

    #[Route('/organizer/{id}', name: 'app_event_delete_organizer', methods: ['POST'])]
    public function deleteOrganizer(Request $request, EventRepository $eventRepository, int $id, EntityManagerInterface $entityManager): Response
    {
        $event = $eventRepository->find($id);
        
        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }

        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            // Delete the image file if it exists
            $image = $event->getImage();
            if ($image) {
                $imagePath = $this->getParameter('images_directory') . '/' . $image;
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $entityManager->remove($event);
            $entityManager->flush();
            
            $this->addFlash('success', 'Event deleted successfully!');
        }

        return $this->redirectToRoute('app_event_index_organizer', [], Response::HTTP_SEE_OTHER);
    }
}