<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\Actor;
use App\Entity\Post;
use App\Entity\Invitation;
use App\Entity\Discussion;
use App\Entity\GroupType;
use App\Form\GroupFormType;
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
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Routing\Annotation\Route;

class GroupController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;
    private string $weatherApiKey;
    private ImageGeneratorService $imageGeneratorService;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        string $weatherApiKey,
        ImageGeneratorService $imageGeneratorService
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->weatherApiKey = $weatherApiKey;
        $this->imageGeneratorService = $imageGeneratorService;
    }

    private function processCoverPicture(Group $group, $coverPicture, bool $forceRegenerate = false): bool
    {
        $this->logger->info("Processing cover picture for group: {$group->getName()} (ID: {$group->getId()}), forceRegenerate: " . ($forceRegenerate ? 'true' : 'false'));

        // If a file is uploaded, process it
        if ($coverPicture) {
            $this->logger->info("Processing uploaded cover picture for group: {$group->getName()}");
            try {
                $binaryContent = file_get_contents($coverPicture->getPathname());
                if ($binaryContent === false) {
                    throw new FileException('Unable to read cover picture file.');
                }
                $group->setCoverPicture($binaryContent);
                $this->logger->info("Successfully set uploaded cover picture for group: {$group->getName()}, size: " . strlen($binaryContent) . " bytes");
                return true;
            } catch (FileException $e) {
                $this->logger->error("Cover picture processing failed for group: {$group->getName()}: " . $e->getMessage());
                $this->addFlash('error', 'Failed to process cover picture: ' . $e->getMessage());
                return false;
            }
        }

        // If no file is uploaded (or force regenerate is true), generate a cover picture if missing
        if ($forceRegenerate || !$group->getCoverPicture()) {
            $this->logger->info("No cover picture exists for group: {$group->getName()} or forceRegenerate is true. Generating new image.");
            $groupType = $group->getGroupType();
            if ($groupType) {
                $eventType = $groupType->getType();
                $this->logger->info("Group type for {$group->getName()}: {$eventType}");
                if (empty($eventType)) {
                    $this->logger->error("Event type is empty for group: {$group->getName()}. Cannot generate cover picture.");
                    return false;
                }
                try {
                    $imageData = $this->imageGeneratorService->generateImageForEventType($eventType);
                    if (empty($imageData)) {
                        $this->logger->error("Generated image data is empty for group: {$group->getName()}");
                        return false;
                    }
                    $group->setCoverPicture($imageData);
                    $this->logger->info("Successfully set generated cover picture for group: {$group->getName()}, size: " . strlen($imageData) . " bytes");
                    return true;
                } catch (\Exception $e) {
                    $this->logger->error("Failed to generate cover picture for group: {$group->getName()} (event type: {$eventType}): " . $e->getMessage());
                    return false;
                }
            } else {
                $this->logger->warning("No group type set for group: {$group->getName()}. Cannot generate cover picture.");
                return false;
            }
        }

        $this->logger->info("Cover picture already exists for group: {$group->getName()}. Skipping generation.");
        return true;
    }

    private function getBase64Image($coverPicture): ?string
    {
        $this->logger->info("Converting cover picture to base64");

        if ($coverPicture === null) {
            $this->logger->warning("Cover picture is null. Cannot convert to base64.");
            return null;
        }

        if (is_resource($coverPicture)) {
            $this->logger->info("Cover picture is a resource. Reading stream.");
            $binaryContent = stream_get_contents($coverPicture, -1, 0);
            if ($binaryContent === false) {
                $this->logger->error("Failed to read cover picture stream.");
                return null;
            }
            $this->logger->info("Successfully read cover picture stream, size: " . strlen($binaryContent) . " bytes");
            fclose($coverPicture);
        } else {
            $this->logger->info("Cover picture is a binary string, size: " . strlen($coverPicture) . " bytes");
            $binaryContent = $coverPicture;
        }

        if (empty($binaryContent)) {
            $this->logger->warning("Cover picture binary content is empty.");
            return null;
        }

        $base64 = base64_encode($binaryContent);
        $this->logger->info("Successfully converted cover picture to base64, length: " . strlen($base64));
        // Log the first few characters of the base64 string to verify it's an image
        $this->logger->debug("Base64 preview: " . substr($base64, 0, 50) . "...");
        return $base64;
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
        $this->logger->info("Creating new event group");
        $group = new Group();
        $form = $this->createForm(GroupFormType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->logger->info("Form submitted and valid for group: {$group->getName()}");
            if (!$this->processCoverPicture($group, $form->get('coverPicture')->getData(), true)) {
                $this->logger->error("Failed to process cover picture during group creation");
                return $this->render('carint/create_event_group.html.twig', [
                    'group_form' => $form->createView(),
                ]);
            }

            $this->entityManager->persist($group);
            try {
                $this->entityManager->flush();
                $this->logger->info("Successfully created group: {$group->getName()} (ID: {$group->getId()})");
                $this->addFlash('success', 'Event group created successfully!');
                return $this->redirectToRoute('group_list');
            } catch (\Exception $e) {
                $this->logger->error("Failed to flush group creation to database: " . $e->getMessage());
                $this->addFlash('error', 'Failed to create event group: ' . $e->getMessage());
            }
        }

        return $this->render('carint/create_event_group.html.twig', [
            'group_form' => $form->createView(),
        ]);
    }

    #[Route('/group/list', name: 'group_list', methods: ['GET'])]
public function list(Request $request, GroupRepository $groupRepository, EntityManagerInterface $entityManager): Response
{
    $this->logger->info("Fetching group list");
    $searchQuery = trim($request->query->get('q', ''));
    $location = trim($request->query->get('location', ''));
    $visibility = trim($request->query->get('visibility', ''));
    $date = trim($request->query->get('date', ''));
    $category = trim($request->query->get('category', ''));

    // Handle special date filters like 'next_7_days'
    $effectiveDate = $date;
    if ($date === 'next_7_days') {
        $startDate = new \DateTime();
        $endDate = (clone $startDate)->modify('+7 days')->setTime(23, 59, 59);
        $effectiveDate = [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')];
        $this->logger->info("Applying date filter for next 7 days: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");
    }

    // Handle category filter
    $categoryId = null;
    if ($category) {
        $groupType = $entityManager->getRepository(GroupType::class)->findOneBy(['type' => $category]);
        if ($groupType) {
            $categoryId = $groupType->getId();
            $this->logger->info("Filtering by category: {$category}, group_type_id: {$categoryId}");
        } else {
            $this->logger->warning("Category not found: {$category}");
        }
    }

    // Pagination parameters
    $limit = 9; // 9 groups per page
    $page = (int) $request->query->get('page', 1); // Current page, default to 1
    $page = max(1, $page); // Ensure page is at least 1
    $offset = ($page - 1) * $limit;

    // Fetch paginated groups with filters
    $groups = $groupRepository->findBySearchQueryWithFilters($searchQuery, $location, $visibility, $effectiveDate, $limit, $offset, $categoryId);
    $locations = $groupRepository->findUniqueLocations();
    $visibilities = $groupRepository->findUniqueVisibilities();
    $upcomingEvents = $groupRepository->findUpcomingEvents(7, 5); // Fetch upcoming events within 7 days

    // Calculate total groups and total pages
    $totalGroups = $groupRepository->countBySearchQueryWithFilters($searchQuery, $location, $visibility, $effectiveDate, $categoryId);
    $totalPages = max(1, (int) ceil($totalGroups / $limit));

    $this->logger->info("Number of groups retrieved: " . count($groups) . ", Total groups: {$totalGroups}, Total pages: {$totalPages}, Current page: {$page}");
    foreach ($groups as $group) {
        $this->logger->info("Processing group: {$group->getName()} (ID: {$group->getId()})");

        // Generate cover picture if missing
        if (!$group->getCoverPicture()) {
            $success = $this->processCoverPicture($group, null, true); // Force regeneration
            if (!$success) {
                $this->logger->error("Failed to process cover picture for group: {$group->getName()}");
            }
        }
    }

    // Flush changes to the database
    try {
        $this->entityManager->flush();
        $this->logger->info("Successfully flushed changes to the database.");
    } catch (\Exception $e) {
        $this->logger->error("Failed to flush changes to the database: " . $e->getMessage());
        throw $e;
    }

    $groupData = [];
    foreach ($groups as $group) {
        $base64Image = $this->getBase64Image($group->getCoverPicture());
        $this->logger->info("Base64 image for group {$group->getName()}: " . ($base64Image ? 'Generated (length: ' . strlen($base64Image) . ')' : 'Null'));
        $groupData[] = [
            'group' => $group,
            'base64Image' => $base64Image,
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
            
            // Process the cover picture: Force regeneration if no cover picture exists
            $uploadedFile = $form->get('coverPicture')->getData();
            $forceRegenerate = !$group->getCoverPicture(); // Regenerate if no cover picture exists
            $this->logger->info("Uploaded file: " . ($uploadedFile ? 'Present' : 'Not present') . ", Force regenerate: " . ($forceRegenerate ? 'true' : 'false'));
            if (!$this->processCoverPicture($group, $uploadedFile, $forceRegenerate)) {
                $this->logger->error("Failed to process cover picture during group edit");
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

        // Generate cover picture if missing
        if (!$group->getCoverPicture()) {
            $this->logger->info("Cover picture missing for group: {$group->getName()}. Generating...");
            $success = $this->processCoverPicture($group, null, true); // Force regeneration
            if ($success) {
                try {
                    $this->entityManager->flush();
                    $this->logger->info("Successfully flushed cover picture for group: {$group->getName()}");
                } catch (\Exception $e) {
                    $this->logger->error("Failed to flush cover picture for group: {$group->getName()}: " . $e->getMessage());
                    throw $e;
                }
            } else {
                $this->logger->error("Failed to generate cover picture for group: {$group->getName()}");
            }
        }

        $base64Image = $this->getBase64Image($group->getCoverPicture());
        $this->logger->info("Base64 image for group {$group->getName()}: " . ($base64Image ? 'Generated (length: ' . strlen($base64Image) . ')' : 'Null'));
        $discussions = $discussionRepository->findBy(['group' => $group], ['createdAt' => 'DESC']);
        $weatherData = $this->fetchWeatherForecast($group);
        $relatedGroups = $this->entityManager->getRepository(Group::class)
            ->findBy(['location' => $group->getLocation()], ['eventDate' => 'DESC'], 3);

        return $this->render('carint/group_view.html.twig', [
            'group' => $group,
            'base64Image' => $base64Image,
            'discussions' => $discussions,
            'weatherData' => $weatherData,
            'relatedGroups' => $relatedGroups,
        ]);
    }

    #[Route('/group/{id}/posts', name: 'group_posts', methods: ['GET'])]
    public function posts(Group $group): Response
    {
        $this->logger->info("Viewing posts for group: {$group->getName()} (ID: {$group->getId()})");

        // Generate cover picture if missing
        if (!$group->getCoverPicture()) {
            $this->logger->info("Cover picture missing for group: {$group->getName()}. Generating...");
            $success = $this->processCoverPicture($group, null, true); // Force regeneration
            if ($success) {
                try {
                    $this->entityManager->flush();
                    $this->logger->info("Successfully flushed cover picture for group: {$group->getName()}");
                } catch (\Exception $e) {
                    $this->logger->error("Failed to flush cover picture for group: {$group->getName()}: " . $e->getMessage());
                    throw $e;
                }
            } else {
                $this->logger->error("Failed to generate cover picture for group: {$group->getName()}");
            }
        }

        $base64Image = $this->getBase64Image($group->getCoverPicture());
        $this->logger->info("Base64 image for group {$group->getName()}: " . ($base64Image ? 'Generated (length: ' . strlen($base64Image) . ')' : 'Null'));
        $posts = $this->entityManager->getRepository(Post::class)
            ->findBy(['group' => $group], ['createdAt' => 'DESC']);

        return $this->render('carint/group_posts.html.twig', [
            'group' => $group,
            'posts' => $posts,
            'base64Image' => $base64Image,
        ]);
    }

    #[Route('/group/{id}/invite', name: 'group_invite', methods: ['GET', 'POST'])]
    public function invite(Request $request, Group $group, InvitationRepository $invitationRepository, ActorRepository $actorRepository): Response
    {
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
                $invitation->setGroup($group)->setInvitee($invitee);
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

        return $this->render('carint/group_invite.html.twig', [
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

    #[Route('/group/{id}/discussion', name: 'group_discussion', methods: ['GET'])]
    public function discussion(Group $group, DiscussionRepository $discussionRepository): Response
    {
        $this->logger->info("Viewing discussions for group: {$group->getName()} (ID: {$group->getId()})");
        $discussions = $discussionRepository->findBy(['group' => $group], ['createdAt' => 'DESC']);
        return $this->render('carint/group_discussion.html.twig', [
            'group' => $group,
            'discussions' => $discussions,
        ]);
    }

    #[Route('/group/{id}/add-discussion', name: 'group_add_discussion', methods: ['POST'])]
    public function addDiscussion(Request $request, Group $group): Response
    {
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