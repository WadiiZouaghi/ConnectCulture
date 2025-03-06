<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\Actor;
use App\Entity\Post;
use App\Entity\Invitation;
use App\Entity\Discussion;
use App\Entity\GroupType;
use App\Form\GroupFormType;
use App\Form\DiscussionType;
use App\Repository\ActorRepository;
use App\Repository\DiscussionRepository;
use App\Repository\GroupRepository;
use App\Repository\InvitationRepository;
use App\Service\ImageGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class GroupController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;
    private string $weatherApiKey;
    private ImageGeneratorService $imageGeneratorService;
    private MailerInterface $mailer;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        string $weatherApiKey,
        ImageGeneratorService $imageGeneratorService,
        MailerInterface $mailer
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->weatherApiKey = $weatherApiKey;
        $this->imageGeneratorService = $imageGeneratorService;
        $this->mailer = $mailer;
    }

    private function processCoverPicture(Group $group, $coverPicture, bool $forceGenerate = false): bool
    {
        try {
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/groups/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            if ($coverPicture instanceof UploadedFile) {
                // Handle uploaded file
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!in_array($coverPicture->getMimeType(), $allowedMimeTypes)) {
                    $this->logger->error("Invalid file type for group: {$group->getName()}. Allowed types: " . implode(', ', $allowedMimeTypes));
                    return false;
                }

                if ($coverPicture->getSize() > 5 * 1024 * 1024) { // 5MB limit
                    $this->logger->error("File too large for group: {$group->getName()}. Max size: 5MB");
                    return false;
                }

                $coverPictureName = uniqid() . '.' . $coverPicture->guessExtension();
                $coverPicture->move($uploadDir, $coverPictureName);
                $group->setCoverPicture($coverPictureName);
                $this->logger->info("Successfully set uploaded cover picture for group: {$group->getName()}, filename: {$coverPictureName}");
                return true;
            }

            // If no file is uploaded (or force generate is true), generate a cover picture if missing
            if ($forceGenerate || !$group->getCoverPicture()) {
                $this->logger->info("No cover picture exists for group: {$group->getName()} or forceGenerate is true. Attempting to generate new image.");
                $groupType = $group->getGroupType();
                if ($groupType) {
                    $eventType = $groupType->getType();
                    $this->logger->info("Group type for {$group->getName()}: {$eventType}");
                    if (empty($eventType)) {
                        $this->logger->error("Event type is empty for group: {$group->getName()}. Cannot generate cover picture.");
                        return $this->useDefaultCoverPicture($group);
                    }
                    try {
                        $imageData = $this->imageGeneratorService->generateImageForEventType($eventType);
                        if (empty($imageData)) {
                            $this->logger->warning("Generated image data is empty for group: {$group->getName()} (event type: {$eventType})");
                            return $this->useDefaultCoverPicture($group);
                        }
                        $coverPictureName = uniqid() . '.png';
                        file_put_contents($uploadDir . $coverPictureName, $imageData);
                        $group->setCoverPicture($coverPictureName);
                        $this->logger->info("Successfully set generated cover picture for group: {$group->getName()}, filename: {$coverPictureName}");
                        return true;
                    } catch (\Exception $e) {
                        $this->logger->error("Failed to generate cover picture for group: {$group->getName()} (event type: {$eventType}): " . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
                        return $this->useDefaultCoverPicture($group);
                    }
                } else {
                    $this->logger->warning("No group type set for group: {$group->getName()}. Cannot generate cover picture.");
                    return $this->useDefaultCoverPicture($group);
                }
            }

            $this->logger->info("Cover picture already exists for group: {$group->getName()}. Skipping generation.");
            return true;
        } catch (\Exception $e) {
            $this->logger->error("Error processing cover picture for group: {$group->getName()}: " . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
            return $this->useDefaultCoverPicture($group);
        }
    }

    private function useDefaultCoverPicture(Group $group): bool
    {
        $this->logger->info("Falling back to default cover picture for group: {$group->getName()}");
        $defaultImagePath = $this->getParameter('kernel.project_dir') . '/public/images/default-cover.jpg';
        if (!file_exists($defaultImagePath)) {
            $this->logger->error("Default cover picture not found at: {$defaultImagePath}");
            return false;
        }

        $group->setCoverPicture('default-cover.jpg'); // Store filename instead of binary
        $this->logger->info("Successfully set default cover picture for group: {$group->getName()}");
        return true;
    }

    private function getBase64Image($coverPicture): ?array
    {
        $this->logger->info("Converting cover picture to base64");

        if ($coverPicture === null) {
            $this->logger->warning("Cover picture is null. Cannot convert to base64.");
            return null;
        }

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/groups/';
        $filePath = $uploadDir . $coverPicture;

        if (!file_exists($filePath)) {
            $this->logger->error("Cover picture file not found at: {$filePath}");
            return null;
        }

        $binaryContent = file_get_contents($filePath);
        if ($binaryContent === false) {
            $this->logger->error("Failed to read cover picture file at: {$filePath}");
            return null;
        }

        $this->logger->info("Successfully read cover picture file, size: " . strlen($binaryContent) . " bytes");

        // Detect the image type
        $imageType = 'image/jpeg'; // Default to JPEG
        $imageHeader = substr($binaryContent, 0, 4);
        if (strncmp($imageHeader, "\xFF\xD8\xFF", 3) === 0) { // JPEG
            $imageType = 'image/jpeg';
            $this->logger->info("Detected JPEG image header.");
        } elseif (strncmp($imageHeader, "\x89PNG", 4) === 0) { // PNG
            $imageType = 'image/png';
            $this->logger->info("Detected PNG image header.");
        } elseif (strncmp($imageHeader, "GIF8", 4) === 0) { // GIF
            $imageType = 'image/gif';
            $this->logger->info("Detected GIF image header.");
        } else {
            $this->logger->warning("Binary content does not appear to be a valid image. First 4 bytes: " . bin2hex($imageHeader));
        }

        $base64 = base64_encode($binaryContent);
        $this->logger->info("Successfully converted cover picture to base64, length: " . strlen($base64));
        $this->logger->debug("Base64 preview: " . substr($base64, 0, 50) . "...");
        return [
            'data' => $base64,
            'type' => $imageType,
        ];
    }

    private function fetchWeatherForecast(Group $group): ?array
    {
        if (empty($this->weatherApiKey)) {
            $this->logger->warning('OpenWeatherMap API key is not configured.');
            return null;
        }

        $httpClient = HttpClient::create();
        try {
            $queryParams = [
                'appid' => $this->weatherApiKey,
                'units' => 'metric',
                'cnt' => 40,
            ];

            if ($group->getLatitude() && $group->getLongitude()) {
                $queryParams['lat'] = $group->getLatitude();
                $queryParams['lon'] = $group->getLongitude();
            } else {
                $location = $group->getLocation();
                if (!$location) {
                    return null;
                }
                $city = explode(',', $location)[0];
                $queryParams['q'] = $city;
            }

            $response = $httpClient->request('GET', 'https://api.openweathermap.org/data/2.5/forecast', [
                'query' => $queryParams,
            ]);

            $weatherData = $response->toArray();
            $eventDate = $group->getEventDate() ? $group->getEventDate()->format('Y-m-d') : null;

            if (!$eventDate) {
                return null;
            }

            $dailyForecasts = [];
            foreach ($weatherData['list'] as $forecast) {
                $forecastDate = substr($forecast['dt_txt'], 0, 10);
                if ($forecastDate === $eventDate) {
                    $dailyForecasts[] = [
                        'date' => $forecast['dt_txt'],
                        'temp' => $forecast['main']['temp'],
                        'description' => $forecast['weather'][0]['description'],
                        'icon' => $forecast['weather'][0]['icon'],
                    ];
                    break;
                }
            }

            return !empty($dailyForecasts) ? ['daily' => $dailyForecasts] : null;
        } catch (\Exception $e) {
            $this->logger->error('Failed to fetch weather data for group ' . ($group->getId() ?? 'unknown') . ': ' . $e->getMessage());
            return null;
        }
    }

    #[Route('/group/create', name: 'group_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $this->logger->info("Attempting to create new event group");

        $actor = $this->getUser();
        if (!$actor) {
            $this->logger->warning("Unauthenticated user attempted to create a group");
            $this->addFlash('error', 'You must be logged in to create an event group.');
            return $this->redirectToRoute('app_login');
        }

        $group = new Group();
        $form = $this->createForm(GroupFormType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->logger->info("Form submitted and valid for group: {$group->getName()}");

            if ($group->getLocation()) {
                try {
                    $client = HttpClient::create();
                    $response = $client->request('GET', 'http://api.openweathermap.org/geo/1.0/direct', [
                        'query' => [
                            'q' => $group->getLocation(),
                            'limit' => 1,
                            'appid' => $this->weatherApiKey,
                        ],
                    ]);
                    $geoData = $response->toArray();
                    if (!empty($geoData)) {
                        $group->setLatitude($geoData[0]['lat']);
                        $group->setLongitude($geoData[0]['lon']);
                        $this->logger->info("Fetched coordinates for group {$group->getName()}: lat={$geoData[0]['lat']}, lon={$geoData[0]['lon']}");
                    } else {
                        $this->logger->warning("No coordinates found for location: {$group->getLocation()}");
                        $this->addFlash('error', "The location '{$group->getLocation()}' could not be found.");
                        return $this->render('carint/create_event_group.html.twig', ['group_form' => $form->createView()]);
                    }
                } catch (\Exception $e) {
                    $this->logger->error("Failed to fetch coordinates for group {$group->getName()}: " . $e->getMessage());
                    $this->addFlash('error', "Failed to validate location '{$group->getLocation()}': " . $e->getMessage());
                    return $this->render('carint/create_event_group.html.twig', ['group_form' => $form->createView()]);
                }
            } else {
                $this->addFlash('error', "Location is required.");
                return $this->render('carint/create_event_group.html.twig', ['group_form' => $form->createView()]);
            }

            $uploadedFile = $form->get('coverPicture')->getData();
            if (!$this->processCoverPicture($group, $uploadedFile)) {
                $this->logger->error("Failed to process cover picture during group creation");
                $this->addFlash('error', "Failed to process the cover picture.");
                return $this->render('carint/create_event_group.html.twig', ['group_form' => $form->createView()]);
            }

            $group->addActor($actor);
            $this->logger->info("Associated actor {$actor->getEmail()} with group: {$group->getName()}");

            $this->entityManager->persist($group);
            try {
                $this->entityManager->flush();
                $this->logger->info("Successfully created group: {$group->getName()} (ID: {$group->getId()}) by actor: {$actor->getEmail()}");

                $email = (new Email())
                    ->from('zouaghi.wadii69@gmail.com')
                    ->to($actor->getEmail())
                    ->subject('New Event Group Created: ' . $group->getName())
                    ->text("You created '{$group->getName()}'.\nLocation: {$group->getLocation()}\nEvent Date: {$group->getEventDate()->format('Y-m-d H:i')}");
                $this->mailer->send($email);
                $this->logger->info("Sent email notification for group: {$group->getName()} to {$actor->getEmail()}");
            } catch (\Exception $e) {
                $this->logger->error("Failed to flush group creation or send email: " . $e->getMessage());
                $this->addFlash('error', 'Failed to create event group: ' . $e->getMessage());
            }

            $this->addFlash('success', 'Event group created successfully!');
            return $this->redirectToRoute('group_list');
        }

        return $this->render('carint/create_event_group.html.twig', [
            'group_form' => $form->createView(),
        ]);
    }

    #[Route('/group/list', name: 'group_list', methods: ['GET'])]
    public function list(Request $request, GroupRepository $groupRepository): Response
    {
        $this->logger->info("Fetching group list");
        $searchQuery = trim($request->query->get('q', ''));
        $location = trim($request->query->get('location', ''));
        $visibility = trim($request->query->get('visibility', ''));
        $date = trim($request->query->get('date', ''));
        $category = trim($request->query->get('category', ''));

        $effectiveDate = $date;
        if ($date === 'next_7_days') {
            $startDate = new \DateTime();
            $endDate = (clone $startDate)->modify('+7 days')->setTime(23, 59, 59);
            $effectiveDate = [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')];
            $this->logger->info("Applying date filter for next 7 days: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");
        }

        $categoryId = null;
        if ($category) {
            $groupType = $this->entityManager->getRepository(GroupType::class)->findOneBy(['type' => $category]);
            if ($groupType) {
                $categoryId = $groupType->getId();
                $this->logger->info("Filtering by category: {$category}, group_type_id: {$categoryId}");
            } else {
                $this->logger->warning("Category not found: {$category}");
            }
        }

        $limit = 9;
        $page = max(1, (int)$request->query->get('page', 1));
        $offset = ($page - 1) * $limit;

        $groups = $groupRepository->findBySearchQueryWithFilters($searchQuery, $location, $visibility, $effectiveDate, $limit, $offset, $categoryId);
        $locations = $groupRepository->findUniqueLocations();
        $visibilities = $groupRepository->findUniqueVisibilities();
        $upcomingEvents = $groupRepository->findUpcomingEvents(7, 5);

        $totalGroups = $groupRepository->countBySearchQueryWithFilters($searchQuery, $location, $visibility, $effectiveDate, $categoryId);
        $totalPages = max(1, (int)ceil($totalGroups / $limit));

        $this->logger->info("Number of groups retrieved: " . count($groups) . ", Total groups: {$totalGroups}, Total pages: {$totalPages}, Current page: {$page}");
        foreach ($groups as $group) {
            if (!$group->getCoverPicture()) {
                $this->processCoverPicture($group, null, true);
            }
        }

        $this->entityManager->flush();

        $groupData = [];
        foreach ($groups as $group) {
            $imageData = $this->getBase64Image($group->getCoverPicture());
            $groupData[] = [
                'group' => $group,
                'imageData' => $imageData,
            ];
        }

        return $this->render('carint/group_list.html.twig', [
            'groupData' => $groupData,
            'locations' => $locations,
            'visibilities' => $visibilities,
            'searchQuery' => $searchQuery,
            'location' => $location,
            'visibility' => $visibility,
            'date' => $date,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalGroups' => $totalGroups,
            'category' => $category,
            'upcomingEvents' => $upcomingEvents,
        ]);
    }

    #[Route('/group/{id}/edit', name: 'group_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Group $group): Response
    {
        $this->logger->info("Editing group: {$group->getName()} (ID: {$group->getId()})");
        $form = $this->createForm(GroupFormType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->logger->info("Form submitted and valid for editing group: {$group->getName()}");
            $uploadedFile = $form->get('coverPicture')->getData();
            $forceGenerate = !$group->getCoverPicture();
            if (!$this->processCoverPicture($group, $uploadedFile, $forceGenerate)) {
                $this->logger->error("Failed to process cover picture during group edit");
                $this->addFlash('error', "Failed to process the cover picture.");
                return $this->render('carint/edit_event_group.html.twig', [
                    'group_form' => $form->createView(),
                    'group' => $group,
                ]);
            }

            try {
                $this->entityManager->flush();
                $this->logger->info("Successfully updated group: {$group->getName()}");
                $this->addFlash('success', 'Event group updated successfully!');
                return $this->redirectToRoute('group_list');
            } catch (\Exception $e) {
                $this->logger->error("Failed to flush group edit to database: " . $e->getMessage());
                $this->addFlash('error', 'Failed to update event group: ' . $e->getMessage());
            }
        }

        return $this->render('carint/edit_event_group.html.twig', [
            'group_form' => $form->createView(),
            'group' => $group,
        ]);
    }

    #[Route('/group/{id}/delete', name: 'group_delete', methods: ['POST'])]
    public function delete(Request $request, Group $group): Response
    {
        $this->logger->info("Deleting group: {$group->getName()} (ID: {$group->getId()})");
        if ($this->isCsrfTokenValid('delete' . $group->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($group);
            try {
                $this->entityManager->flush();
                $this->logger->info("Successfully deleted group: {$group->getName()}");
                $this->addFlash('success', 'Event group deleted successfully!');
            } catch (\Exception $e) {
                $this->logger->error("Failed to delete group: {$group->getName()}: " . $e->getMessage());
                $this->addFlash('error', 'Failed to delete event group: ' . $e->getMessage());
            }
        } else {
            $this->logger->error("Invalid CSRF token during group deletion for group: {$group->getName()}");
            $this->addFlash('error', 'Invalid CSRF token.');
        }

        return $this->redirectToRoute('group_list');
    }
    #[Route('/group/{id}/view', name: 'group_view', methods: ['GET'])]
public function view(Group $group, DiscussionRepository $discussionRepository): Response
{
    $this->logger->info("Viewing group: {$group->getName()} (ID: {$group->getId()})");

    if (!$group->getCoverPicture()) {
        $this->logger->info("Cover picture missing for group: {$group->getName()}. Generating...");
        $this->processCoverPicture($group, null, true);
        $this->entityManager->flush();
    }

    // Fetch coordinates if missing
    $weatherErrorMessage = null;
    if (!$group->getLatitude() || !$group->getLongitude()) {
        try {
            $client = HttpClient::create();
            $response = $client->request('GET', 'http://api.openweathermap.org/geo/1.0/direct', [
                'query' => [
                    'q' => $group->getLocation(),
                    'limit' => 1,
                    'appid' => $this->weatherApiKey,
                ],
            ]);
            $geoData = $response->toArray();
            if (!empty($geoData)) {
                $group->setLatitude($geoData[0]['lat']);
                $group->setLongitude($geoData[0]['lon']);
                $this->entityManager->persist($group);
                $this->entityManager->flush();
                $this->logger->info("Updated coordinates for group {$group->getId()}: lat={$geoData[0]['lat']}, lon={$geoData[0]['lon']}");
            } else {
                $this->logger->warning("No coordinates found for location: {$group->getLocation()}");
                $weatherErrorMessage = "Weather data not available: The location '{$group->getLocation()}' could not be found.";
            }
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch coordinates for group {$group->getId()}: " . $e->getMessage());
            $weatherErrorMessage = "Weather data not available: Failed to fetch coordinates for the location.";
        }
    }

    $imageData = $this->getBase64Image($group->getCoverPicture());
    $discussions = $discussionRepository->findBy(['group' => $group], ['createdAt' => 'DESC']);
    $weatherData = $this->fetchWeatherForecast($group);
    $relatedGroups = $this->entityManager->getRepository(Group::class)
        ->findBy(['location' => $group->getLocation()], ['eventDate' => 'DESC'], 3);

    return $this->render('carint/group_view.html.twig', [
        'group' => $group,
        'imageData' => $imageData,
        'discussions' => $discussions,
        'weatherData' => $weatherData,
        'weatherErrorMessage' => $weatherErrorMessage, // Add this variable to the template
        'relatedGroups' => $relatedGroups,
    ]);
}
    #[Route('/group/{id}/posts', name: 'group_posts', methods: ['GET'])]
    public function posts(Group $group): Response
    {
        $this->logger->info("Viewing posts for group: {$group->getName()} (ID: {$group->getId()})");

        if (!$group->getCoverPicture()) {
            $this->logger->info("Cover picture missing for group: {$group->getName()}. Generating...");
            $this->processCoverPicture($group, null, true);
            $this->entityManager->flush();
        }

        $imageData = $this->getBase64Image($group->getCoverPicture());
        $posts = $this->entityManager->getRepository(Post::class)
            ->findBy(['group' => $group], ['createdAt' => 'DESC']);

        return $this->render('carint/group_posts.html.twig', [
            'group' => $group,
            'posts' => $posts,
            'imageData' => $imageData,
        ]);
    }

    #[Route('/group/{id}/invite', name: 'group_invite', methods: ['GET', 'POST'])]
    public function invite(Request $request, Group $group, InvitationRepository $invitationRepository, ActorRepository $actorRepository): Response
    {
        $actor = $this->getUser();
        if ($request->isMethod('POST') && !$actor) {
            $this->addFlash('error', 'You must be logged in to send an invitation.');
            return $this->redirectToRoute('app_login');
        }

        $this->logger->info("Inviting user to group: {$group->getName()} (ID: {$group->getId()})");
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('invite' . $group->getId(), $request->request->get('_token'))) {
                $this->logger->error("Invalid CSRF token during group invite for group: {$group->getName()}");
                $this->addFlash('error', 'Invalid CSRF token.');
                return $this->redirectToRoute('group_view', ['id' => $group->getId()]);
            }

            $inviteeEmail = filter_var($request->request->get('invitee_email'), FILTER_SANITIZE_EMAIL);
            if (!$inviteeEmail) {
                $this->logger->error("Invitee email is required for group: {$group->getName()}");
                $this->addFlash('error', 'Invitee email is required.');
                return $this->redirectToRoute('group_view', ['id' => $group->getId()]);
            }

            $invitee = $actorRepository->findOneBy(['email' => $inviteeEmail]);
            if ($invitee && !$group->isMember($invitee)) {
                $invitation = new Invitation();
                $invitation->setGroup($group)->setInvitee($invitee)->setInviter($actor);
                $this->entityManager->persist($invitation);
                try {
                    $this->entityManager->flush();
                    $this->logger->info("Successfully sent invitation to {$inviteeEmail} for group: {$group->getName()}");
                    $this->addFlash('success', 'Invitation sent successfully!');
                } catch (\Exception $e) {
                    $this->logger->error("Failed to send invitation to {$inviteeEmail} for group: {$group->getName()}: " . $e->getMessage());
                    $this->addFlash('error', 'Failed to send invitation: ' . $e->getMessage());
                }
            } else {
                $this->logger->error("User {$inviteeEmail} not found or already a member of group: {$group->getName()}");
                $this->addFlash('error', 'User not found or already a member.');
            }

            return $this->redirectToRoute('group_view', ['id' => $group->getId()]);
        }

        return $this->render('carint/group_invite_request.html.twig', [
            'group' => $group,
        ]);
    }

    #[Route('/invitation/{id}/accept', name: 'invitation_accept', methods: ['GET', 'POST'])]
    public function acceptInvitation(Invitation $invitation): Response
    {
        $groupName = $invitation->getGroup()->getName();
        $this->logger->info("Accepting invitation for group: {$groupName} (ID: {$invitation->getId()})");
        if ($invitation->getStatus() === 'pending') {
            $invitation->setStatus('accepted');
            try {
                $this->entityManager->flush();
                $this->logger->info("Successfully accepted invitation for group: {$groupName}");
                $this->addFlash('success', 'Invitation accepted! You are now a member of the group.');
            } catch (\Exception $e) {
                $this->logger->error("Failed to accept invitation for group: {$groupName}: " . $e->getMessage());
                $this->addFlash('error', 'Failed to accept invitation: ' . $e->getMessage());
            }
        } else {
            $this->logger->error("Invitation for group {$groupName} is no longer pending");
            $this->addFlash('error', 'Invitation is no longer pending.');
        }

        return $this->redirectToRoute('group_list');
    }

    #[Route('/invitation/{id}/decline', name: 'invitation_decline', methods: ['GET', 'POST'])]
    public function declineInvitation(Invitation $invitation): Response
    {
        $groupName = $invitation->getGroup()->getName();
        $this->logger->info("Declining invitation for group: {$groupName} (ID: {$invitation->getId()})");
        if ($invitation->getStatus() === 'pending') {
            $invitation->setStatus('declined');
            try {
                $this->entityManager->flush();
                $this->logger->info("Successfully declined invitation for group: {$groupName}");
                $this->addFlash('success', 'Invitation declined.');
            } catch (\Exception $e) {
                $this->logger->error("Failed to decline invitation for group: {$groupName}: " . $e->getMessage());
                $this->addFlash('error', 'Failed to decline invitation: ' . $e->getMessage());
            }
        } else {
            $this->logger->error("Invitation for group {$groupName} is no longer pending");
            $this->addFlash('error', 'Invitation is no longer pending.');
        }

        return $this->redirectToRoute('group_list');
    }

    #[Route('/group/{id}/invite-request', name: 'group_invite_request', methods: ['GET'])]
    public function inviteRequest(Group $group): Response
    {
        $this->logger->info("Viewing invite request for group: {$group->getName()} (ID: {$group->getId()})");
        return $this->render('carint/group_invite_request.html.twig', [
            'group' => $group,
        ]);
    }

    #[Route('/group/{id}/invite-request-submit', name: 'group_invite_request_submit', methods: ['POST'])]
    public function submitInviteRequest(Group $group, InvitationRepository $invitationRepository): Response
    {
        $this->logger->info("Submitting invite request for group: {$group->getName()} (ID: {$group->getId()})");
        if (!$group->isPublic()) {
            $existingInvitation = $invitationRepository->findOneBy(['group' => $group, 'status' => 'pending']);
            if (!$existingInvitation) {
                $invitation = new Invitation();
                $invitation->setGroup($group)->setStatus('pending');
                $this->entityManager->persist($invitation);
                try {
                    $this->entityManager->flush();
                    $this->logger->info("Successfully submitted invite request for group: {$group->getName()}");
                    $this->addFlash('success', 'Your invitation request has been sent to the group admin.');
                } catch (\Exception $e) {
                    $this->logger->error("Failed to submit invite request for group: {$group->getName()}: " . $e->getMessage());
                    $this->addFlash('error', 'Failed to submit invitation request: ' . $e->getMessage());
                }
            } else {
                $this->logger->info("User already has a pending invitation request for group: {$group->getName()}");
                $this->addFlash('info', 'You already have a pending invitation request for this group.');
            }
        } else {
            $this->logger->error("Group {$group->getName()} is public, no invitation required");
            $this->addFlash('error', 'This group is public, no invitation required.');
        }

        return $this->redirectToRoute('group_list');
    }

    #[Route('/group/{id}/discussion', name: 'group_discussion', methods: ['GET', 'POST'])]
    public function groupDiscussion(Request $request, Group $group): Response
    {
        $discussions = $this->entityManager->getRepository(Discussion::class)->findBy(['group' => $group]);

        $actor = $this->getUser();
        if ($request->isMethod('POST') && !$actor) {
            $this->addFlash('error', 'You must be logged in to add a discussion.');
            return $this->redirectToRoute('app_login');
        }

        $discussion = new Discussion();
        $discussion->setGroup($group);
        $discussion->setAuthor($actor);
        $discussion->setCreatedAt(new \DateTimeImmutable());

        $form = $this->createForm(DiscussionType::class, $discussion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($discussion);
            $this->entityManager->flush();

            $this->addFlash('success', 'Discussion added successfully!');
            return $this->redirectToRoute('group_discussion', ['id' => $group->getId()]);
        }

        return $this->render('carint/group_discussion.html.twig', [
            'group' => $group,
            'discussions' => $discussions,
            'group_form' => $form->createView(),
            'searchQuery' => $request->query->get('q', ''),
        ]);
    }

    #[Route('/group/{id}/add-discussion', name: 'group_add_discussion', methods: ['POST'])]
    public function addDiscussion(Request $request, Group $group): Response
    {
        $actor = $this->getUser();
        if (!$actor) {
            $this->addFlash('error', 'You must be logged in to add a discussion.');
            return $this->redirectToRoute('app_login');
        }

        $this->logger->info("Adding discussion for group: {$group->getName()} (ID: {$group->getId()})");
        if (!$this->isCsrfTokenValid('add_discussion' . $group->getId(), $request->request->get('_token'))) {
            $this->logger->error("Invalid CSRF token during discussion addition for group: {$group->getName()}");
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('group_discussion', ['id' => $group->getId()]);
        }

        $content = trim($request->request->get('content', ''));
        if (!empty($content)) {
            $discussion = new Discussion();
            $discussion->setContent($content)
                       ->setGroup($group)
                       ->setAuthor($actor)
                       ->setCreatedAt(new \DateTime());
            $this->entityManager->persist($discussion);
            try {
                $this->entityManager->flush();
                $this->logger->info("Successfully added discussion for group: {$group->getName()}");
                $this->addFlash('success', 'Discussion added successfully!');
            } catch (\Exception $e) {
                $this->logger->error("Failed to add discussion for group: {$group->getName()}: " . $e->getMessage());
                $this->addFlash('error', 'Failed to add discussion: ' . $e->getMessage());
            }
        } else {
            $this->logger->error("Discussion content is empty for group: {$group->getName()}");
            $this->addFlash('error', 'Discussion content cannot be empty.');
        }

        return $this->redirectToRoute('group_discussion', ['id' => $group->getId()]);
    }
}