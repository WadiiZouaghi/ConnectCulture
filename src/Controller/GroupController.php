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
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class GroupController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    #[Route('/group/create', name: 'group_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $group = new Group();
        $form = $this->createForm(GroupFormType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coverPicture = $form->get('coverPicture')->getData();
            if ($coverPicture) {
                try {
                    $binaryContent = file_get_contents($coverPicture->getPathname());
                    if ($binaryContent === false) {
                        throw new FileException('Unable to read cover picture file.');
                    }
                    $group->setCoverPicture($binaryContent);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to process cover picture: ' . $e->getMessage());
                    $this->logger->error('Cover picture processing failed: ' . $e->getMessage());
                    return $this->render('carint/create_event_group.html.twig', [
                        'group_form' => $form->createView(),
                    ]);
                }
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
    public function list(Request $request, GroupRepository $groupRepository): Response
    {
        $searchQuery = $request->query->get('q', '');
        $location = $request->query->get('location', '');
        $visibility = $request->query->get('visibility', '');
        $date = $request->query->get('date', '');

        $groups = $groupRepository->findBySearchQueryWithFilters($searchQuery, $location, $visibility, $date);
        $locations = $groupRepository->findUniqueLocations();
        $visibilities = $groupRepository->findUniqueVisibilities();

        return $this->render('carint/group_list.html.twig', [
            'groups' => $groups,
            'searchQuery' => $searchQuery,
            'location' => $location,
            'visibility' => $visibility,
            'date' => $date,
            'locations' => $locations,
            'visibilities' => $visibilities,
        ]);
    }

    #[Route('/group/{id}/edit', name: 'group_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Group $group): Response
    {
        $form = $this->createForm(GroupFormType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coverPicture = $form->get('coverPicture')->getData();
            if ($coverPicture) {
                try {
                    $binaryContent = file_get_contents($coverPicture->getPathname());
                    if ($binaryContent === false) {
                        throw new FileException('Unable to read cover picture file.');
                    }
                    $group->setCoverPicture($binaryContent);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to process cover picture: ' . $e->getMessage());
                    $this->logger->error('Cover picture processing failed: ' . $e->getMessage());
                    return $this->render('carint/edit_event_group.html.twig', [
                        'group_form' => $form->createView(),
                        'group' => $group,
                    ]);
                }
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
        }

        return $this->redirectToRoute('group_list');
    }

    #[Route('/group/{id}/view', name: 'group_view', methods: ['GET'])]
    public function view(Group $group, DiscussionRepository $discussionRepository): Response
    {
        $base64Image = null;
        if ($group->getCoverPicture() !== null) {
            $binaryContent = stream_get_contents($group->getCoverPicture());
            if ($binaryContent !== false && $binaryContent !== '') {
                $base64Image = base64_encode($binaryContent);
            }
        }

        $discussions = $discussionRepository->findBy(['group' => $group], ['createdAt' => 'DESC']);

        return $this->render('carint/group_view.html.twig', [
            'group' => $group,
            'base64Image' => $base64Image,
            'discussions' => $discussions,
        ]);
    }

    #[Route('/group/{id}/posts', name: 'group_posts', methods: ['GET'])]
public function posts(Group $group): Response
{
    $posts = $this->entityManager->getRepository(Post::class)->findBy(['group' => $group], ['createdAt' => 'DESC']);

    $base64Image = null;
    if ($group->getCoverPicture() !== null) {
        $binaryContent = stream_get_contents($group->getCoverPicture());
        if ($binaryContent !== false && $binaryContent !== '') {
            $base64Image = base64_encode($binaryContent);
        }
    }

    return $this->render('carint/group_posts.html.twig', [
        'group' => $group,
        'posts' => $posts,
        'base64Image' => $base64Image,
    ]);
}


    #[Route('/group/{id}/invite', name: 'group_invite', methods: ['GET', 'POST'])]
    public function invite(Request $request, Group $group, InvitationRepository $invitationRepository): Response
    {
        $inviteeEmail = $request->request->get('invitee_email', 'invited@example.com');
        $invitee = $this->entityManager->getRepository(Actor::class)->findOneBy(['email' => $inviteeEmail]);

        if ($invitee && !$group->isMember($invitee)) {
            $invitation = new Invitation();
            $invitation->setGroup($group);
            $invitation->setInvitee($invitee);
            $this->entityManager->persist($invitation);
            $this->entityManager->flush();
            $this->addFlash('success', 'Invitation sent successfully!');
        } else {
            $this->addFlash('error', 'User not found or already a member.');
        }

        return $this->redirectToRoute('group_view', ['id' => $group->getId()]);
    }

    #[Route('/invitation/{id}/accept', name: 'invitation_accept', methods: ['GET', 'POST'])]
    public function acceptInvitation(Request $request, Invitation $invitation): Response
    {
        if ($invitation->getStatus() === 'pending') {
            $invitation->setStatus('accepted');
            $this->entityManager->flush();
            $this->addFlash('success', 'Invitation accepted! You are now a member of the group (anonymous).');
        }

        return $this->redirectToRoute('group_list');
    }

    #[Route('/invitation/{id}/decline', name: 'invitation_decline', methods: ['GET', 'POST'])]
    public function declineInvitation(Request $request, Invitation $invitation): Response
    {
        if ($invitation->getStatus() === 'pending') {
            $invitation->setStatus('declined');
            $this->entityManager->flush();
            $this->addFlash('success', 'Invitation declined (anonymous).');
        }

        return $this->redirectToRoute('group_list');
    }

    #[Route('/group/{id}/invite-request', name: 'group_invite_request', methods: ['GET'])]
    public function inviteRequest(Group $group): Response
    {
        return $this->render('carint/group_invite_request.html.twig', ['group' => $group]);
    }

    #[Route('/group/{id}/invite-request-submit', name: 'group_invite_request_submit', methods: ['POST'])]
    public function submitInviteRequest(Request $request, Group $group, InvitationRepository $invitationRepository): Response
    {
        if (!$group->isPublic()) {
            $existingInvitation = $invitationRepository->findOneBy(['group' => $group, 'status' => 'pending']);
            if (!$existingInvitation) {
                $invitation = new Invitation();
                $invitation->setGroup($group)->setStatus('pending');
                $this->entityManager->persist($invitation);
                $this->entityManager->flush();
                $this->addFlash('success', 'Your invitation request has been sent to the group admin (anonymous).');
            } else {
                $this->addFlash('info', 'You already have a pending invitation request for this group.');
            }
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
            $discussion->setContent($content);
            $discussion->setGroup($group);
            $discussion->setCreatedAt(new \DateTime());
            $this->entityManager->persist($discussion);
            $this->entityManager->flush();
            $this->addFlash('success', 'Discussion added successfully!');
        } else {
            $this->addFlash('error', 'Discussion content cannot be empty.');
        }

        return $this->redirectToRoute('group_discussion', ['id' => $group->getId()]);
    }
}