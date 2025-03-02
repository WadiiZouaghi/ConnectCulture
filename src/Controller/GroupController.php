<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\Actor;
use App\Entity\Post;
use App\Entity\Invitation;
use App\Entity\Discussion;
use App\Form\GroupFormType;
use App\Repository\DiscussionRepository;
use App\Repository\GroupRepository;
use App\Repository\InvitationRepository;
use App\Service\AISuggestionService;
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
    private AISuggestionService $aiSuggestionService;
    private string $weatherApiKey;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        AISuggestionService $aiSuggestionService
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->aiSuggestionService = $aiSuggestionService;
        $this->weatherApiKey = $_ENV['OPENWEATHERMAP_API_KEY'] ?? '';
    }

    /**
     * Process and set the cover picture for a group entity.
     */
    private function processCoverPicture(Group $group, $coverPicture): bool
    {
        if (!$coverPicture) {
            return true;
        }

        try {
            $binaryContent = file_get_contents($coverPicture->getPathname());
            if ($binaryContent === false) {
                throw new FileException('Unable to read cover picture file.');
            }
            $group->setCoverPicture($binaryContent);
            return true;
        } catch (FileException $e) {
            $this->logger->error('Cover picture processing failed: ' . $e->getMessage());
            $this->addFlash('error', 'Failed to process cover picture: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Convert binary cover picture to base64 string.
     *
     * @param mixed $coverPicture The cover picture (binary data or null).
     * @return string|null The base64-encoded string or null if conversion fails.
     */
    private function getBase64Image($coverPicture): ?string
    {
        if ($coverPicture === null) {
            return null;
        }

        if (is_resource($coverPicture)) {
            $binaryContent = stream_get_contents($coverPicture, -1, 0);
            if ($binaryContent === false) {
                $this->logger->error('Failed to read cover picture stream for group.');
                return null;
            }
            fclose($coverPicture);
        } else {
            $binaryContent = $coverPicture;
        }

        return $binaryContent !== '' ? base64_encode($binaryContent) : null;
    }

    /**
     * Fetch weather forecast for a given group.
     */
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
        $group = new Group();
        $form = $this->createForm(GroupFormType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->processCoverPicture($group, $form->get('coverPicture')->getData())) {
                return $this->render('carint/create_event_group.html.twig', [
                    'group_form' => $form->createView(),
                ]);
            }

            $this->entityManager->persist($group);
            $this->entityManager->flush();
            $this->addFlash('success', 'Event group created successfully!');
            return $this->redirectToRoute('group_list');
        }

        return $this->render('carint/create_event_group.html.twig', [
            'group_form' => $form->createView(),
        ]);
    }

    #[Route('/groups', name: 'group_list', methods: ['GET'])]
    public function list(Request $request, GroupRepository $groupRepository, AISuggestionService $aiSuggestionService): Response
    {
        $searchQuery = trim($request->query->get('q', ''));
        $location = trim($request->query->get('location', ''));
        $visibility = trim($request->query->get('visibility', ''));
        $date = trim($request->query->get('date', ''));

        $groups = $groupRepository->findBySearchQueryWithFilters($searchQuery, $location, $visibility, $date);
        $locations = $groupRepository->findUniqueLocations();
        $visibilities = $groupRepository->findUniqueVisibilities();

        // Debug: Log the number of groups retrieved
        $this->logger->info('Number of groups retrieved: ' . count($groups));
        foreach ($groups as $group) {
            $this->logger->info('Group: ' . $group->getName());
        }

        // Generate AI suggestions for the user
        $user = $this->getUser();
        $aiSuggestions = $aiSuggestionService->generateSuggestions($user);

        return $this->render('carint/group_list.html.twig', [
            'groups' => $groups,
            'locations' => $locations,
            'visibilities' => $visibilities,
            'searchQuery' => $searchQuery,
            'location' => $location,
            'visibility' => $visibility,
            'date' => $date,
            'ai_suggestions' => $aiSuggestions,
        ]);
    }

    #[Route('/group/{id}/edit', name: 'group_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Group $group): Response
    {
        $form = $this->createForm(GroupFormType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->processCoverPicture($group, $form->get('coverPicture')->getData())) {
                return $this->render('carint/edit_event_group.html.twig', [
                    'group_form' => $form->createView(),
                    'group' => $group,
                ]);
            }

            $this->entityManager->flush();
            $this->addFlash('success', 'Event group updated successfully!');
            return $this->redirectToRoute('group_list');
        }

        return $this->render('carint/edit_event_group.html.twig', [
            'group_form' => $form->createView(),
            'group' => $group,
        ]);
    }

    #[Route('/group/{id}/delete', name: 'group_delete', methods: ['POST'])]
    public function delete(Request $request, Group $group): Response
    {
        if ($this->isCsrfTokenValid('delete' . $group->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($group);
            $this->entityManager->flush();
            $this->addFlash('success', 'Event group deleted successfully!');
        } else {
            $this->addFlash('error', 'Invalid CSRF token.');
        }

        return $this->redirectToRoute('group_list');
    }

    #[Route('/group/{id}/view', name: 'group_view', methods: ['GET'])]
    public function view(Group $group, DiscussionRepository $discussionRepository): Response
    {
        $base64Image = $this->getBase64Image($group->getCoverPicture());
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
        $posts = $this->entityManager->getRepository(Post::class)
            ->findBy(['group' => $group], ['createdAt' => 'DESC']);
        $base64Image = $this->getBase64Image($group->getCoverPicture());

        return $this->render('carint/group_posts.html.twig', [
            'group' => $group,
            'posts' => $posts,
            'base64Image' => $base64Image,
        ]);
    }

    #[Route('/group/{id}/invite', name: 'group_invite', methods: ['GET', 'POST'])]
    public function invite(Request $request, Group $group, InvitationRepository $invitationRepository): Response
    {
        $inviteeEmail = filter_var($request->request->get('invitee_email', 'invited@example.com'), FILTER_SANITIZE_EMAIL);
        $invitee = $this->entityManager->getRepository(Actor::class)->findOneBy(['email' => $inviteeEmail]);

        if ($invitee && !$group->isMember($invitee)) {
            $invitation = new Invitation();
            $invitation->setGroup($group)->setInvitee($invitee);
            $this->entityManager->persist($invitation);
            $this->entityManager->flush();
            $this->addFlash('success', 'Invitation sent successfully!');
        } else {
            $this->addFlash('error', 'User not found or already a member.');
        }

        return $this->redirectToRoute('group_view', ['id' => $group->getId()]);
    }

    #[Route('/invitation/{id}/accept', name: 'invitation_accept', methods: ['GET', 'POST'])]
    public function acceptInvitation(Invitation $invitation): Response
    {
        if ($invitation->getStatus() === 'pending') {
            $invitation->setStatus('accepted');
            $this->entityManager->flush();
            $this->addFlash('success', 'Invitation accepted! You are now a member of the group.');
        } else {
            $this->addFlash('error', 'Invitation is no longer pending.');
        }

        return $this->redirectToRoute('group_list');
    }

    #[Route('/invitation/{id}/decline', name: 'invitation_decline', methods: ['GET', 'POST'])]
    public function declineInvitation(Invitation $invitation): Response
    {
        if ($invitation->getStatus() === 'pending') {
            $invitation->setStatus('declined');
            $this->entityManager->flush();
            $this->addFlash('success', 'Invitation declined.');
        } else {
            $this->addFlash('error', 'Invitation is no longer pending.');
        }

        return $this->redirectToRoute('group_list');
    }

    #[Route('/group/{id}/invite-request', name: 'group_invite_request', methods: ['GET'])]
    public function inviteRequest(Group $group): Response
    {
        return $this->render('carint/group_invite_request.html.twig', [
            'group' => $group,
        ]);
    }

    #[Route('/group/{id}/invite-request-submit', name: 'group_invite_request_submit', methods: ['POST'])]
    public function submitInviteRequest(Group $group, InvitationRepository $invitationRepository): Response
    {
        if (!$group->isPublic()) {
            $existingInvitation = $invitationRepository->findOneBy(['group' => $group, 'status' => 'pending']);
            if (!$existingInvitation) {
                $invitation = new Invitation();
                $invitation->setGroup($group)->setStatus('pending');
                $this->entityManager->persist($invitation);
                $this->entityManager->flush();
                $this->addFlash('success', 'Your invitation request has been sent to the group admin.');
            } else {
                $this->addFlash('info', 'You already have a pending invitation request for this group.');
            }
        } else {
            $this->addFlash('error', 'This group is public, no invitation required.');
        }

        return $this->redirectToRoute('group_list');
    }

    #[Route('/group/{id}/discussion', name: 'group_discussion', methods: ['GET'])]
    public function discussion(Group $group, DiscussionRepository $discussionRepository): Response
    {
        $discussions = $discussionRepository->findBy(['group' => $group], ['createdAt' => 'DESC']);
        return $this->render('carint/group_discussion.html.twig', [
            'group' => $group,
            'discussions' => $discussions,
        ]);
    }

    #[Route('/group/{id}/add-discussion', name: 'group_add_discussion', methods: ['POST'])]
    public function addDiscussion(Request $request, Group $group): Response
    {
        $content = trim($request->request->get('content', ''));
        if (!empty($content)) {
            $discussion = new Discussion();
            $discussion->setContent($content)
                       ->setGroup($group)
                       ->setCreatedAt(new \DateTime());
            $this->entityManager->persist($discussion);
            $this->entityManager->flush();
            $this->addFlash('success', 'Discussion added successfully!');
        } else {
            $this->addFlash('error', 'Discussion content cannot be empty.');
        }

        return $this->redirectToRoute('group_discussion', ['id' => $group->getId()]);
    }
}